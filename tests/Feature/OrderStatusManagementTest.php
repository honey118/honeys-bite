<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class OrderStatusManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_regular_user_cannot_use_admin_order_actions(): void
    {
        $order = $this->order();

        $this->get(route('admin.orders.index'))->assertRedirect('/login');
        $this->post(route('admin.orders.confirm', $order))->assertRedirect('/login');
        $this->post(route('admin.orders.complete', $order))->assertRedirect('/login');

        $this->actingAs(User::factory()->create());
        $this->get(route('admin.orders.index'))->assertForbidden();
        $this->get(route('admin.orders.show', $order))->assertForbidden();
        $this->post(route('admin.orders.confirm', $order))->assertForbidden();
        $this->post(route('admin.orders.reject', $order), ['rejection_reason' => 'Alasan'])->assertForbidden();
        $this->post(route('admin.orders.mark-cod-paid', $order))->assertForbidden();
        $this->post(route('admin.orders.complete', $order))->assertForbidden();
    }

    public function test_admin_can_confirm_cod_for_delivery_and_pickup_allocating_stock_without_marking_paid(): void
    {
        $admin = $this->admin();
        $delivery = $this->order(['fulfillment_method' => 'delivery']);
        $pickup = $this->order(['fulfillment_method' => 'pickup']);
        $this->actingAs($admin);

        $this->get(route('admin.orders.index'))->assertOk()->assertSee($delivery->order_number);
        $this->get(route('admin.orders.show', $delivery))->assertOk()->assertSee($delivery->customer->name);

        $this->post(route('admin.orders.confirm', $delivery))->assertRedirect();
        $this->post(route('admin.orders.confirm', $pickup))->assertRedirect();

        $this->assertSame(Order::STATUS_IN_TRANSIT, $delivery->fresh()->status);
        $this->assertSame(Order::STATUS_READY_FOR_PICKUP, $pickup->fresh()->status);
        $this->assertSame(Order::PAYMENT_UNPAID, $delivery->fresh()->payment_status);
        $this->assertSame(Order::PAYMENT_UNPAID, $pickup->fresh()->payment_status);
        $this->assertTrue($delivery->fresh()->stock_allocated);
        $this->assertTrue($pickup->fresh()->stock_allocated);
        $this->assertSame(8, $delivery->item->product->fresh()->stock);
        $this->assertSame(8, $pickup->item->product->fresh()->stock);
    }

    public function test_admin_rejection_of_unallocated_order_does_not_change_stock_or_allow_repeated_transitions(): void
    {
        $product = $this->product(['stock' => 8]);
        $order = $this->order(['payment_method' => 'bank_transfer'], $product, 3);
        $this->actingAs($this->admin());

        $this->post(route('admin.orders.reject', $order), ['rejection_reason' => ''])
            ->assertSessionHasErrors(['rejection_reason']);
        $this->assertSame(8, $product->fresh()->stock);

        $this->post(route('admin.orders.reject', $order), ['rejection_reason' => 'Produk tidak tersedia.'])->assertRedirect();
        $this->assertSame(Order::STATUS_REJECTED, $order->fresh()->status);
        $this->assertSame('Produk tidak tersedia.', $order->fresh()->rejection_reason);
        $this->assertFalse($order->fresh()->stock_allocated);
        $this->assertFalse($order->fresh()->stock_restored);
        $this->assertSame(8, $product->fresh()->stock);

        $this->post(route('admin.orders.reject', $order), ['rejection_reason' => 'Alasan kedua'])
            ->assertSessionHasErrors(['order']);
        $this->post(route('admin.orders.confirm', $order))->assertSessionHasErrors(['order']);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_rejection_restores_previously_allocated_stock_only_once(): void
    {
        $product = $this->product(['stock' => 11]);
        $order = $this->order([], $product, 3);
        $product->decrement('stock', 3);
        $order->update(['stock_allocated' => true]);
        $this->actingAs($this->admin());

        $this->post(route('admin.orders.reject', $order), ['rejection_reason' => 'Pesanan tidak dapat dipenuhi.'])
            ->assertRedirect();

        $this->assertSame(11, $product->fresh()->stock);
        $this->assertTrue($order->fresh()->stock_restored);
        $this->post(route('admin.orders.reject', $order), ['rejection_reason' => 'Alasan kedua'])
            ->assertSessionHasErrors(['order']);
        $this->assertSame(11, $product->fresh()->stock);
    }

    public function test_legacy_order_with_unknown_allocation_state_cannot_change_stock(): void
    {
        $product = $this->product(['stock' => 8]);
        $order = $this->order([], $product, 2);
        $order->update(['stock_allocated' => null]);
        $this->actingAs($this->admin());

        $this->post(route('admin.orders.confirm', $order))
            ->assertSessionHasErrors(['order']);
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);

        $this->post(route('admin.orders.reject', $order), ['rejection_reason' => 'Perlu rekonsiliasi.'])
            ->assertSessionHasErrors(['order']);
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_confirmation_fails_without_changing_stock_or_pending_status_when_stock_is_insufficient(): void
    {
        $product = $this->product(['stock' => 1]);
        $order = $this->order([], $product, 2);
        $this->actingAs($this->admin());

        $this->post(route('admin.orders.confirm', $order))
            ->assertSessionHasErrors(['stock']);

        $this->assertSame(1, $product->fresh()->stock);
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertFalse($order->fresh()->stock_allocated);
    }

    public function test_repeated_confirmation_does_not_allocate_stock_twice(): void
    {
        $product = $this->product(['stock' => 5]);
        $order = $this->order([], $product, 3);
        $this->actingAs($this->admin());

        $this->post(route('admin.orders.confirm', $order))->assertRedirect();
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertTrue($order->fresh()->stock_allocated);

        $this->post(route('admin.orders.confirm', $order))->assertSessionHasErrors(['order']);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_failed_confirmation_transaction_rolls_back_stock_allocation(): void
    {
        $product = $this->product(['stock' => 7]);
        $order = $this->order([], $product, 2);
        $this->actingAs($this->admin());

        Order::updating(static function (Order $model): void {
            if ($model->status !== Order::STATUS_PENDING) {
                throw new RuntimeException('Simulated order update failure.');
            }
        });
        $this->withoutExceptionHandling();

        try {
            $this->post(route('admin.orders.confirm', $order));
            $this->fail('Expected the simulated persistence exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated order update failure.', $exception->getMessage());
        }

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertFalse($order->fresh()->stock_allocated);
    }

    public function test_customer_sees_rejection_reason_and_cannot_pay_rejected_order(): void
    {
        $customer = User::factory()->create();
        $order = $this->order(['payment_method' => 'bank_transfer'], null, 2, $customer);
        $this->actingAs($this->admin());
        $this->post(route('admin.orders.reject', $order), ['rejection_reason' => 'Pesanan tidak dapat dipenuhi.']);

        $this->actingAs($customer);
        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Pesanan tidak dapat dipenuhi.')
            ->assertDontSee('SIMULASI DEMO');
        $this->post(route('orders.simulate-payment', $order))->assertSessionHasErrors(['payment']);
        $this->assertSame(Order::PAYMENT_UNPAID, $order->fresh()->payment_status);
    }

    public function test_transfer_payment_is_hidden_until_admin_confirmation_and_demo_payment_updates_states(): void
    {
        $customer = User::factory()->create();
        $product = $this->product(['stock' => 5]);
        $order = $this->order(['payment_method' => 'bank_transfer'], $product, 1, $customer);
        $this->actingAs($customer);

        $this->get(route('orders.show', $order))->assertDontSee('SIMULASI DEMO');
        $this->post(route('orders.simulate-payment', $order))->assertSessionHasErrors(['payment']);

        $this->actingAs($this->admin());
        $this->post(route('admin.orders.confirm', $order))->assertRedirect();
        $this->assertSame(Order::STATUS_AWAITING_PAYMENT, $order->fresh()->status);
        $this->assertSame(4, $product->fresh()->stock);

        $this->actingAs($customer);
        $this->get(route('orders.show', $order))->assertSee('SIMULASI DEMO');
        $this->post(route('orders.simulate-payment', $order))->assertRedirect();

        $this->assertSame(Order::PAYMENT_DEMO_PAID, $order->fresh()->payment_status);
        $this->assertSame(Order::STATUS_READY_FOR_PICKUP, $order->fresh()->status);
        $this->assertSame(4, $product->fresh()->stock);
        $this->post(route('orders.simulate-payment', $order))->assertSessionHasErrors(['payment']);
    }

    public function test_qris_demo_delivery_enters_transit_only_after_simulated_payment(): void
    {
        $customer = User::factory()->create();
        $order = $this->order([
            'payment_method' => 'qris_demo',
            'fulfillment_method' => 'delivery',
        ], null, 1, $customer);

        $this->actingAs($this->admin());
        $this->post(route('admin.orders.confirm', $order));
        $this->assertSame(Order::STATUS_AWAITING_PAYMENT, $order->fresh()->status);
        $this->assertSame(9, $order->item->product->fresh()->stock);

        $this->actingAs($customer);
        $this->get(route('orders.show', $order))->assertSee('QRIS');
        $this->post(route('orders.simulate-payment', $order));

        $this->assertSame(Order::PAYMENT_DEMO_PAID, $order->fresh()->payment_status);
        $this->assertSame(Order::STATUS_IN_TRANSIT, $order->fresh()->status);
        $this->assertSame(9, $order->item->product->fresh()->stock);
    }

    public function test_only_owner_can_simulate_payment(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->order(['payment_method' => 'qris_demo'], null, 1, $owner);
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.orders.confirm', $order));

        $this->actingAs($other)
            ->post(route('orders.simulate-payment', $order))
            ->assertNotFound();
        $this->assertSame(Order::PAYMENT_UNPAID, $order->fresh()->payment_status);
    }

    public function test_admin_can_mark_confirmed_cod_as_paid_once(): void
    {
        $order = $this->order(['payment_method' => 'cod']);
        $this->actingAs($this->admin());

        $this->post(route('admin.orders.mark-cod-paid', $order))->assertSessionHasErrors(['order']);
        $this->post(route('admin.orders.confirm', $order));
        $this->post(route('admin.orders.mark-cod-paid', $order))->assertRedirect();
        $this->assertSame(Order::PAYMENT_PAID, $order->fresh()->payment_status);
        $this->post(route('admin.orders.mark-cod-paid', $order))->assertSessionHasErrors(['order']);
    }

    public function test_admin_can_complete_fulfillment_orders_without_changing_payment_or_stock_allocation(): void
    {
        $admin = $this->admin();
        $product = $this->product(['stock' => 7]);
        $delivery = $this->order([
            'status' => Order::STATUS_IN_TRANSIT,
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_UNPAID,
            'stock_allocated' => true,
        ], $product, 2);
        $pickup = $this->order([
            'status' => Order::STATUS_READY_FOR_PICKUP,
            'payment_method' => 'qris_demo',
            'payment_status' => Order::PAYMENT_DEMO_PAID,
            'stock_allocated' => true,
        ], $product, 1);
        $product->decrement('stock', 3);
        $this->actingAs($admin);

        $this->get(route('admin.orders.show', $delivery))
            ->assertOk()
            ->assertSee('Tandai pesanan selesai');
        $this->post(route('admin.orders.complete', $delivery))->assertRedirect();
        $this->post(route('admin.orders.complete', $pickup))->assertRedirect();

        $this->assertSame(Order::STATUS_COMPLETED, $delivery->fresh()->status);
        $this->assertSame(Order::STATUS_COMPLETED, $pickup->fresh()->status);
        $this->assertSame(Order::PAYMENT_UNPAID, $delivery->fresh()->payment_status);
        $this->assertSame(Order::PAYMENT_DEMO_PAID, $pickup->fresh()->payment_status);
        $this->assertTrue($delivery->fresh()->stock_allocated);
        $this->assertTrue($pickup->fresh()->stock_allocated);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_admin_can_mark_cod_paid_after_completing_order_without_changing_order_status(): void
    {
        $order = $this->order([
            'status' => Order::STATUS_COMPLETED,
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_UNPAID,
            'stock_allocated' => true,
        ]);
        $this->actingAs($this->admin());

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Tandai pembayaran diterima');
        $this->post(route('admin.orders.mark-cod-paid', $order))->assertRedirect();

        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertSame(Order::PAYMENT_PAID, $order->fresh()->payment_status);
        $this->assertTrue($order->fresh()->stock_allocated);
    }

    public function test_admin_cannot_complete_orders_outside_fulfillment_stage_or_complete_twice(): void
    {
        $admin = $this->admin();
        $orders = [
            $this->order(['status' => Order::STATUS_PENDING]),
            $this->order(['status' => Order::STATUS_AWAITING_PAYMENT]),
            $this->order(['status' => Order::STATUS_REJECTED]),
            $this->order(['status' => Order::STATUS_COMPLETED]),
        ];
        $this->actingAs($admin);

        foreach ($orders as $order) {
            $this->get(route('admin.orders.show', $order))
                ->assertOk()
                ->assertDontSee('Tandai pesanan selesai');
            $this->post(route('admin.orders.complete', $order))
                ->assertSessionHasErrors(['order']);
            $this->assertSame($order->status, $order->fresh()->status);
        }
    }

    public function test_customer_can_see_completed_status_in_order_detail_and_history(): void
    {
        $customer = User::factory()->create();
        $order = $this->order([
            'status' => Order::STATUS_IN_TRANSIT,
            'stock_allocated' => true,
        ], null, 2, $customer);
        $this->actingAs($this->admin())
            ->post(route('admin.orders.complete', $order));

        $this->actingAs($customer);

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee(Order::STATUS_COMPLETED);
        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee(Order::STATUS_COMPLETED);
    }

    public function test_failed_rejection_transaction_does_not_restore_stock_or_partially_update_order(): void
    {
        $product = $this->product(['stock' => 7]);
        $order = $this->order([], $product, 2);
        $product->decrement('stock', 2);
        $order->update(['stock_allocated' => true]);
        $this->actingAs($this->admin());

        Order::updating(static function (Order $model): void {
            if ($model->status === Order::STATUS_REJECTED) {
                throw new RuntimeException('Simulated order update failure.');
            }
        });
        $this->withoutExceptionHandling();

        try {
            $this->post(route('admin.orders.reject', $order), ['rejection_reason' => 'Tidak tersedia.']);
            $this->fail('Expected the simulated persistence exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated order update failure.', $exception->getMessage());
        }

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertFalse($order->fresh()->stock_restored);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_rejection_fails_safely_if_the_allocated_product_is_missing(): void
    {
        $order = $this->order();
        $order->update(['stock_allocated' => true]);
        $order->item()->update(['product_id' => null]);
        $this->actingAs($this->admin());

        $this->post(route('admin.orders.reject', $order), ['rejection_reason' => 'Tidak tersedia.'])
            ->assertSessionHasErrors(['order']);

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertFalse($order->fresh()->stock_restored);
    }

    private function order(array $overrides = [], ?Product $product = null, int $quantity = 2, ?User $customer = null): Order
    {
        $product ??= $this->product();
        $customer ??= User::factory()->create();
        $subtotal = ((float) $product->price) * $quantity;

        $order = $customer->orders()->create(array_merge([
            'order_number' => 'HB-'.strtoupper((string) str()->ulid()),
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'delivery_fee' => '0.00',
            'total' => number_format($subtotal, 2, '.', ''),
            'fulfillment_method' => 'pickup',
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_UNPAID,
            'status' => Order::STATUS_PENDING,
            'stock_allocated' => false,
            'stock_restored' => false,
        ], $overrides));

        $order->item()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'subtotal' => number_format($subtotal, 2, '.', ''),
        ]);

        return $order;
    }

    private function product(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Roti Batch 4',
            'description' => 'Produk untuk tes transisi.',
            'price' => '1000.00',
            'stock' => 10,
            'is_active' => true,
        ], $attributes));
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();

        return $admin;
    }
}
