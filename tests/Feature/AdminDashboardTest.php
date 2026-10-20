<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_paid_and_demo_payment_once_and_excludes_unpaid_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $product = Product::factory()->create(['stock' => 0, 'is_active' => true]);
        Product::factory()->create(['stock' => 5, 'is_active' => true]);
        Product::factory()->create(['stock' => 6, 'is_active' => true]);
        Product::factory()->create(['stock' => 1, 'is_active' => false]);

        $this->createOrder($customer, $product, Order::STATUS_PENDING, Order::PAYMENT_UNPAID, '1000.00');
        $this->createOrder($customer, $product, Order::STATUS_PENDING, Order::PAYMENT_UNPAID, '2000.00');
        $this->createOrder($customer, $product, Order::STATUS_REJECTED, Order::PAYMENT_UNPAID, '3000.00');
        $codOrder = $this->createOrder(
            $customer,
            $product,
            Order::STATUS_IN_TRANSIT,
            Order::PAYMENT_UNPAID,
            '10000.00',
            '2500.00',
        );
        $this->createOrder($customer, $product, Order::STATUS_COMPLETED, Order::PAYMENT_DEMO_PAID, '5000.00');
        $this->createOrder($customer, $product, Order::STATUS_AWAITING_PAYMENT, Order::PAYMENT_UNPAID, '6000.00');
        $this->createOrder($customer, $product, Order::STATUS_COMPLETED, Order::PAYMENT_PAID, '7000.00', '1000.00');
        $this->createOrder($otherCustomer, $product, Order::STATUS_COMPLETED, Order::PAYMENT_PAID, '4000.00', '500.00');
        $this->createOrder($customer, $product, Order::STATUS_COMPLETED, Order::PAYMENT_UNPAID, '9000.00');

        $this->actingAs($admin)
            ->post(route('admin.orders.mark-cod-paid', $codOrder))
            ->assertRedirect();

        $this->assertSame(Order::PAYMENT_PAID, $codOrder->fresh()->payment_status);

        $response = $this->get(route('admin.home'));

        $response->assertOk()
            ->assertSee('Dashboard Admin')
            ->assertSee('Total produk aktif')
            ->assertSee('Pesanan perlu ditangani')
            ->assertSee('Total penjualan aktual')
            ->assertSee('Rp30.000,00');
        $this->assertSame([
            'active_products' => 3,
            'total_orders' => 9,
            'pending_orders' => 2,
            'paid_sales' => '30000',
            'low_stock_products' => 2,
        ], $response->viewData('stats'));
    }

    public function test_admin_dashboard_shows_zero_statistics_when_there_are_no_products_or_orders(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->get(route('admin.home'));

        $response->assertOk()
            ->assertSee('Rp0,00')
            ->assertSee('Produk aktif dengan stok menipis');
        $this->assertSame([
            'active_products' => 0,
            'total_orders' => 0,
            'pending_orders' => 0,
            'paid_sales' => '0',
            'low_stock_products' => 0,
        ], $response->viewData('stats'));
    }

    private function createOrder(
        User $customer,
        Product $product,
        string $status,
        string $paymentStatus,
        string $subtotal,
        string $deliveryFee = '0.00',
    ): Order {
        $total = number_format((float) $subtotal + (float) $deliveryFee, 2, '.', '');
        $order = $customer->orders()->create([
            'order_number' => 'HB-'.strtoupper((string) str()->ulid()),
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'fulfillment_method' => 'delivery',
            'payment_method' => 'cod',
            'payment_status' => $paymentStatus,
            'status' => $status,
            'stock_allocated' => false,
            'stock_restored' => false,
        ]);

        $order->item()->create([
            'product_id' => $product->getKey(),
            'product_name' => $product->name,
            'unit_price' => $subtotal,
            'quantity' => 1,
            'subtotal' => $subtotal,
        ]);

        return $order;
    }
}
