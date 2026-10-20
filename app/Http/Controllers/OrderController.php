<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use LogicException;

class OrderController extends Controller
{
    public function create(Product $product): View|RedirectResponse
    {
        abort_unless($product->is_active, 404);

        if ($product->stock < 1) {
            return redirect()->route('products.show', $product)
                ->with('error', 'Stok produk habis. Silakan pilih produk lain.');
        }

        return view('orders.checkout', compact('product'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'fulfillment_method' => ['required', Rule::in(['pickup', 'delivery'])],
            'payment_method' => ['required', Rule::in(['cod', 'bank_transfer', 'qris_demo'])],
            'recipient_name' => ['exclude_unless:fulfillment_method,delivery', 'required', 'string', 'max:255'],
            'recipient_phone' => ['exclude_unless:fulfillment_method,delivery', 'required', 'string', 'max:40'],
            'delivery_address' => ['exclude_unless:fulfillment_method,delivery', 'required', 'string', 'max:2000'],
        ]);

        $quantity = (int) $validated['quantity'];
        $orderId = DB::transaction(function () use ($request, $product, $validated, $quantity): int {
            $lockedProduct = Product::query()->lockForUpdate()->find($product->getKey());

            if (! $lockedProduct || ! $lockedProduct->is_active) {
                throw ValidationException::withMessages([
                    'product' => 'Produk ini sudah tidak tersedia.',
                ]);
            }

            // Checkout hanya memeriksa ketersediaan; stok baru dialokasikan saat admin mengonfirmasi pesanan.
            if ($lockedProduct->stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stok tidak mencukupi untuk jumlah yang dipilih.',
                ]);
            }

            $unitPriceCents = $this->decimalToCents($lockedProduct->price);
            $deliveryFeeCents = $validated['fulfillment_method'] === 'delivery'
                ? $this->decimalToCents(AppSetting::deliveryFee())
                : 0;

            if ($unitPriceCents > 0 && $quantity > intdiv(PHP_INT_MAX - $deliveryFeeCents, $unitPriceCents)) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah pesanan terlalu besar.',
                ]);
            }

            $subtotalCents = $unitPriceCents * $quantity;
            $totalCents = $subtotalCents + $deliveryFeeCents;

            $order = Order::create([
                'user_id' => $request->user()->getKey(),
                'order_number' => 'HB-'.Str::upper((string) Str::ulid()),
                'subtotal' => $this->centsToDecimal($subtotalCents),
                'delivery_fee' => $this->centsToDecimal($deliveryFeeCents),
                'total' => $this->centsToDecimal($totalCents),
                'fulfillment_method' => $validated['fulfillment_method'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => Order::PAYMENT_UNPAID,
                'recipient_name' => $validated['recipient_name'] ?? null,
                'recipient_phone' => $validated['recipient_phone'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'status' => Order::STATUS_PENDING,
                'stock_allocated' => false,
            ]);

            $order->item()->create([
                'product_id' => $lockedProduct->getKey(),
                'product_name' => $lockedProduct->name,
                'unit_price' => $lockedProduct->price,
                'quantity' => $quantity,
                'subtotal' => $this->centsToDecimal($subtotalCents),
            ]);

            return $order->getKey();
        }, attempts: 3);

        return redirect()->route('orders.show', $orderId)->with('order_created', true);
    }

    public function index(Request $request): View
    {
        $orders = $request->user()->orders()
            ->with('item')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, int $order): View
    {
        $order = $request->user()->orders()
            ->with('item.product')
            ->findOrFail($order);
        $product = $order->item?->product;
        $canReviewProduct = $order->status === Order::STATUS_COMPLETED
            && $product !== null
            && ! $product->reviews()
                ->where('user_id', $request->user()->getKey())
                ->exists();

        return view('orders.show', compact('order', 'canReviewProduct'));
    }

    public function simulatePayment(Request $request, int $order): RedirectResponse
    {
        DB::transaction(function () use ($request, $order): void {
            $lockedOrder = $request->user()->orders()
                ->lockForUpdate()
                ->with('item')
                ->findOrFail($order);

            if (! in_array($lockedOrder->payment_method, ['bank_transfer', 'qris_demo'], true)
                || $lockedOrder->payment_status !== Order::PAYMENT_UNPAID
                || $lockedOrder->status !== Order::STATUS_AWAITING_PAYMENT
                || $lockedOrder->stock_restored) {
                throw ValidationException::withMessages([
                    'payment' => 'Simulasi pembayaran belum tersedia untuk pesanan ini.',
                ]);
            }

            $lockedOrder->update([
                'payment_status' => Order::PAYMENT_DEMO_PAID,
                'status' => $lockedOrder->fulfillment_method === 'delivery'
                    ? Order::STATUS_IN_TRANSIT
                    : Order::STATUS_READY_FOR_PICKUP,
            ]);
        }, attempts: 3);

        return back()->with('success', 'Simulasi pembayaran demo berhasil.');
    }

    private function decimalToCents(string $amount): int
    {
        if (! preg_match('/\A(\d+)(?:\.(\d{1,2}))?\z/', $amount, $parts)) {
            throw new LogicException('Money values must be non-negative decimal strings with at most two decimal places.');
        }

        return ((int) $parts[1] * 100) + (int) str_pad($parts[2] ?? '', 2, '0');
    }

    private function centsToDecimal(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
