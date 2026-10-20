<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderManagementController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->with(['customer', 'item'])
            ->latest()
            ->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'item']);

        return view('admin.orders.show', compact('order'));
    }

    public function confirm(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order): void {
            // Kunci pesanan dan produk menjaga alokasi serta perubahan status tetap atomik saat konfirmasi berulang.
            $lockedOrder = Order::query()->lockForUpdate()->with('item')->findOrFail($order->getKey());
            if ($lockedOrder->status !== Order::STATUS_PENDING || $lockedOrder->stock_restored) {
                throw ValidationException::withMessages([
                    'order' => 'Hanya pesanan yang menunggu konfirmasi yang dapat diproses.',
                ]);
            }

            if ($lockedOrder->stock_allocated === null) {
                throw ValidationException::withMessages([
                    'order' => 'Status alokasi stok pesanan lama belum diketahui. Rekonsiliasi stok diperlukan sebelum pesanan dapat diproses.',
                ]);
            }

            if (! $lockedOrder->stock_allocated) {
                $item = $lockedOrder->item;
                if (! $item || $item->product_id === null) {
                    throw ValidationException::withMessages([
                        'order' => 'Produk pesanan tidak ditemukan; stok tidak dapat dialokasikan dengan aman.',
                    ]);
                }

                $product = Product::query()->lockForUpdate()->find($item->product_id);
                if (! $product) {
                    throw ValidationException::withMessages([
                        'order' => 'Produk pesanan tidak ditemukan; stok tidak dapat dialokasikan dengan aman.',
                    ]);
                }

                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => 'Stok produk terbaru tidak mencukupi. Pesanan tetap menunggu konfirmasi dan stok tidak berubah.',
                    ]);
                }
                $product->decrement('stock', $item->quantity);
            }

            $fulfillmentStatus = $lockedOrder->fulfillment_method === 'delivery'
                ? Order::STATUS_IN_TRANSIT
                : Order::STATUS_READY_FOR_PICKUP;

            $lockedOrder->update([
                'status' => $lockedOrder->payment_method === 'cod'
                    ? $fulfillmentStatus
                    : Order::STATUS_AWAITING_PAYMENT,
                'stock_allocated' => true,
            ]);
        }, attempts: 3);

        return back()->with('success', 'Pesanan berhasil dikonfirmasi.');
    }

    public function reject(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $stockRestored = DB::transaction(function () use ($order, $validated): bool {
            $lockedOrder = Order::query()->lockForUpdate()->with('item')->findOrFail($order->getKey());

            if ($lockedOrder->status !== Order::STATUS_PENDING || $lockedOrder->stock_restored) {
                throw ValidationException::withMessages([
                    'order' => 'Hanya pesanan yang menunggu konfirmasi dan belum dipulihkan stoknya yang dapat ditolak.',
                ]);
            }

            if ($lockedOrder->stock_allocated === null) {
                throw ValidationException::withMessages([
                    'order' => 'Status alokasi stok pesanan lama belum diketahui. Rekonsiliasi stok diperlukan sebelum pesanan dapat ditolak.',
                ]);
            }

            // Stok hanya dipulihkan bila sebelumnya benar-benar dialokasikan; transaksi dan kunci mencegah pemulihan ganda.
            if ($lockedOrder->stock_allocated) {
                $item = $lockedOrder->item;
                if (! $item || $item->product_id === null) {
                    throw ValidationException::withMessages([
                        'order' => 'Detail produk yang stoknya telah dialokasikan tidak ditemukan; penolakan dihentikan agar stok tidak salah dihitung.',
                    ]);
                }

                $product = Product::query()->lockForUpdate()->find($item->product_id);
                if (! $product) {
                    throw ValidationException::withMessages([
                        'order' => 'Produk yang stoknya telah dialokasikan tidak ditemukan; penolakan dihentikan agar stok tidak salah dihitung.',
                    ]);
                }

                $product->increment('stock', $item->quantity);
            }

            $lockedOrder->update([
                'status' => Order::STATUS_REJECTED,
                'payment_status' => Order::PAYMENT_UNPAID,
                'rejection_reason' => $validated['rejection_reason'],
                'stock_restored' => $lockedOrder->stock_allocated,
            ]);

            return $lockedOrder->stock_allocated;
        }, attempts: 3);

        return back()->with(
            'success',
            $stockRestored ? 'Pesanan ditolak dan stok telah dipulihkan.' : 'Pesanan ditolak tanpa perubahan stok.'
        );
    }

    public function markCodPaid(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());
            $fulfillmentStatus = $lockedOrder->fulfillment_method === 'delivery'
                ? Order::STATUS_IN_TRANSIT
                : Order::STATUS_READY_FOR_PICKUP;

            if ($lockedOrder->payment_method !== 'cod'
                || $lockedOrder->payment_status !== Order::PAYMENT_UNPAID
                || ! in_array($lockedOrder->status, [
                    $fulfillmentStatus,
                    Order::STATUS_COMPLETED,
                ], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Pembayaran COD hanya dapat ditandai untuk pesanan yang sudah dikonfirmasi dan belum dibayar.',
                ]);
            }

            $lockedOrder->update(['payment_status' => Order::PAYMENT_PAID]);
        }, attempts: 3);

        return back()->with('success', 'Pembayaran COD ditandai telah diterima.');
    }

    public function complete(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if (! in_array($lockedOrder->status, [
                Order::STATUS_IN_TRANSIT,
                Order::STATUS_READY_FOR_PICKUP,
            ], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Hanya pesanan dalam tahap pemenuhan yang dapat ditandai selesai.',
                ]);
            }

            $lockedOrder->update(['status' => Order::STATUS_COMPLETED]);
        }, attempts: 3);

        return back()->with('success', 'Pesanan ditandai selesai.');
    }
}
