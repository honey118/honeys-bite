<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 5;

    public function index(): View
    {
        $productStats = DB::table('products')
            ->selectRaw('COUNT(CASE WHEN is_active = ? THEN 1 END) AS active_products', [true])
            ->selectRaw(
                'COUNT(CASE WHEN is_active = ? AND stock <= ? THEN 1 END) AS low_stock_products',
                [true, self::LOW_STOCK_THRESHOLD],
            )
            ->first();

        $orderStats = DB::table('orders')
            ->selectRaw('COUNT(*) AS total_orders')
            ->selectRaw(
                'COUNT(CASE WHEN status = ? THEN 1 END) AS pending_orders',
                [Order::STATUS_PENDING],
            )
            // Pembayaran demo ikut dihitung untuk laporan ini; status selesai saja tidak membuktikan pembayaran diterima.
            // total sudah menyimpan subtotal dan ongkir, sehingga tiap pesanan cukup dijumlahkan satu kali.
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN payment_status IN (?, ?) THEN total ELSE 0 END), 0) AS paid_sales',
                [Order::PAYMENT_PAID, Order::PAYMENT_DEMO_PAID],
            )
            ->first();

        $stats = [
            'active_products' => (int) $productStats->active_products,
            'total_orders' => (int) $orderStats->total_orders,
            'pending_orders' => (int) $orderStats->pending_orders,
            'paid_sales' => (string) $orderStats->paid_sales,
            'low_stock_products' => (int) $productStats->low_stock_products,
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
