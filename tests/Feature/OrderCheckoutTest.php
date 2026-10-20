<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_checkout_or_access_order_history(): void
    {
        $product = $this->product();

        $this->get(route('checkout.create', $product))->assertRedirect('/login');
        $this->post(route('checkout.store', $product), [])->assertRedirect('/login');
        $this->get(route('orders.index'))->assertRedirect('/login');
        $this->get('/orders/1')->assertRedirect('/login');
    }

    public function test_customer_can_open_checkout_for_an_active_product_with_stock(): void
    {
        $this->actingAs(User::factory()->create());
        $product = $this->product(['stock' => 1]);

        $this->get(route('checkout.create', $product))
            ->assertOk()
            ->assertSee('Lengkapi pesanan Anda')
            ->assertSee('name="quantity"', false);
    }

    public function test_checkout_redirects_to_product_detail_when_stock_is_zero(): void
    {
        $this->actingAs(User::factory()->create());
        $product = $this->product(['stock' => 0]);

        $this->get(route('checkout.create', $product))
            ->assertRedirect(route('products.show', $product))
            ->assertSessionHas('error', 'Stok produk habis. Silakan pilih produk lain.');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Stok produk habis. Silakan pilih produk lain.')
            ->assertDontSee('Lengkapi pesanan Anda');
    }

    public function test_pickup_checkout_uses_database_price_zero_delivery_fee_and_does_not_allocate_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['price' => '1250.50', 'stock' => 6]);
        $this->actingAs($user);

        $response = $this->post(route('checkout.store', $product), [
            'quantity' => '2',
            'fulfillment_method' => 'pickup',
            'payment_method' => 'cod',
            'price' => '1.00',
            'subtotal' => '0.01',
            'delivery_fee' => '90000.00',
            'total' => '0.01',
            'status' => 'Selesai',
            'user_id' => 999999,
        ]);

        $order = Order::with('item')->firstOrFail();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame($user->id, $order->user_id);
        $this->assertMatchesRegularExpression('/^HB-[0-9A-HJKMNP-TV-Z]{26}$/', $order->order_number);
        $this->assertSame('2501.00', $order->subtotal);
        $this->assertSame('0.00', $order->delivery_fee);
        $this->assertSame('2501.00', $order->total);
        $this->assertSame('Menunggu Konfirmasi', $order->status);
        $this->assertSame('Roti Uji', $order->item->product_name);
        $this->assertSame('1250.50', $order->item->unit_price);
        $this->assertSame(2, $order->item->quantity);
        $this->assertSame(6, $product->fresh()->stock);
        $this->assertFalse($order->stock_allocated);
        $this->assertDatabaseHas('orders', ['order_number' => $order->order_number]);
    }

    public function test_delivery_requires_recipient_details_and_uses_configured_fee(): void
    {
        AppSetting::query()->create([
            'key' => AppSetting::DELIVERY_FEE_KEY,
            'value' => '2750.25',
        ]);
        $user = User::factory()->create();
        $product = $this->product(['price' => '3000.00', 'stock' => 5]);
        $this->actingAs($user);

        $this->from(route('checkout.create', $product))
            ->post(route('checkout.store', $product), [
                'quantity' => 2,
                'fulfillment_method' => 'delivery',
            ])
            ->assertRedirect(route('checkout.create', $product))
            ->assertSessionHasErrors(['recipient_name', 'recipient_phone', 'delivery_address']);

        $this->post(route('checkout.store', $product), [
            'quantity' => 2,
            'fulfillment_method' => 'delivery',
            'payment_method' => 'bank_transfer',
            'recipient_name' => 'Penerima Uji',
            'recipient_phone' => '081234567890',
            'delivery_address' => 'Jalan Roti Nomor 1',
            'payment_method' => 'bank_transfer',
            'delivery_fee' => '0',
            'total' => '1',
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame('6000.00', $order->subtotal);
        $this->assertSame('2750.25', $order->delivery_fee);
        $this->assertSame('8750.25', $order->total);
        $this->assertSame('Penerima Uji', $order->recipient_name);
        $this->assertSame('Jalan Roti Nomor 1', $order->delivery_address);
    }

    public function test_delivery_checkout_uses_the_five_thousand_default_when_setting_is_missing(): void
    {
        $this->actingAs(User::factory()->create());
        $product = $this->product(['price' => '3000.00', 'stock' => 5]);

        $this->post(route('checkout.store', $product), $this->deliveryPayload())
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame('5000.00', $order->delivery_fee);
        $this->assertSame('11000.00', $order->total);
    }

    public function test_delivery_fee_changes_apply_only_to_orders_created_after_the_change(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product(['price' => '3000.00', 'stock' => 10]);
        AppSetting::query()->create([
            'key' => AppSetting::DELIVERY_FEE_KEY,
            'value' => '2750.25',
        ]);
        $this->actingAs($customer);

        $this->post(route('checkout.store', $product), $this->deliveryPayload())->assertRedirect();
        $oldOrder = Order::firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.settings.delivery-fee.update'), ['delivery_fee' => '9000.00'])
            ->assertRedirect(route('admin.settings.delivery-fee.edit'));

        $this->actingAs($customer)
            ->post(route('checkout.store', $product), $this->deliveryPayload())
            ->assertRedirect();

        $newOrder = Order::query()->latest('id')->firstOrFail();
        $this->assertSame('2750.25', $oldOrder->fresh()->delivery_fee);
        $this->assertSame('9000.00', $newOrder->delivery_fee);
    }

    public function test_archived_or_out_of_stock_product_is_rejected_without_creating_order(): void
    {
        $user = User::factory()->create();
        $archived = $this->product(['is_active' => false]);
        $empty = $this->product(['stock' => 0]);
        $this->actingAs($user);

        $this->get(route('checkout.create', $archived))->assertNotFound();
        $this->post(route('checkout.store', $archived), $this->pickupPayload())->assertSessionHasErrors(['product']);
        $this->post(route('checkout.store', $empty), $this->pickupPayload(['quantity' => 1]))
            ->assertSessionHasErrors(['quantity']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(0, $empty->fresh()->stock);
    }

    public function test_non_positive_fractional_and_over_stock_quantities_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $product = $this->product(['stock' => 3]);

        foreach (['0', '-1', '1.5', '4'] as $quantity) {
            $this->post(route('checkout.store', $product), $this->pickupPayload(['quantity' => $quantity]))
                ->assertSessionHasErrors(['quantity']);
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_order_numbers_are_unique(): void
    {
        $this->actingAs(User::factory()->create());
        $product = $this->product(['stock' => 4]);

        $this->post(route('checkout.store', $product), $this->pickupPayload(['quantity' => 1]));
        $this->post(route('checkout.store', $product), $this->pickupPayload(['quantity' => 1]));

        $this->assertSame(2, Order::count());
        $this->assertSame(2, Order::query()->distinct()->count('order_number'));
    }

    public function test_order_history_and_detail_are_limited_to_the_owner_and_keep_snapshots(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = $this->product(['name' => 'Roti Asli', 'price' => '1100.25', 'stock' => 4]);
        $this->actingAs($owner);

        $this->post(route('checkout.store', $product), $this->pickupPayload(['quantity' => 2]));
        $order = Order::with('item')->firstOrFail();

        $product->update(['name' => 'Nama Baru', 'price' => '9999.99']);

        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Roti Asli')
            ->assertDontSee('Pesanan Rahasia');

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Roti Asli')
            ->assertSee('Rp1.100,25');

        $this->get(route('orders.index'))->assertSee('Rp2.200,50');

        $otherOrder = $otherUser->orders()->create([
            'order_number' => 'HB-'.strtoupper((string) str()->ulid()),
            'subtotal' => '100.00',
            'delivery_fee' => '0.00',
            'total' => '100.00',
            'fulfillment_method' => 'pickup',
            'status' => Order::STATUS_PENDING,
        ]);
        $otherOrder->item()->create([
            'product_id' => $product->id,
            'product_name' => 'Pesanan Rahasia',
            'unit_price' => '100.00',
            'quantity' => 1,
            'subtotal' => '100.00',
        ]);

        $this->get(route('orders.show', $otherOrder))->assertNotFound();
        $this->get(route('orders.index'))->assertDontSee($otherOrder->order_number);

        $this->assertSame('Roti Asli', $order->item->product_name);
        $this->assertSame('1100.25', $order->item->unit_price);
    }

    public function test_failed_order_item_creation_rolls_back_order_without_changing_stock(): void
    {
        $this->actingAs(User::factory()->create());
        $product = $this->product(['stock' => 5]);

        OrderItem::creating(static function (): void {
            throw new RuntimeException('Simulated item persistence failure.');
        });

        $this->withoutExceptionHandling();

        try {
            $this->post(route('checkout.store', $product), $this->pickupPayload(['quantity' => 2]));
            $this->fail('Expected the simulated persistence exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated item persistence failure.', $exception->getMessage());
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    private function product(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Roti Uji',
            'description' => 'Roti untuk pengujian.',
            'price' => '1000.00',
            'stock' => 10,
            'is_active' => true,
        ], $attributes));
    }

    private function pickupPayload(array $overrides = []): array
    {
        return array_merge([
            'quantity' => 2,
            'fulfillment_method' => 'pickup',
            'payment_method' => 'cod',
        ], $overrides);
    }

    private function deliveryPayload(array $overrides = []): array
    {
        return array_merge([
            'quantity' => 2,
            'fulfillment_method' => 'delivery',
            'payment_method' => 'cod',
            'recipient_name' => 'Penerima Uji',
            'recipient_phone' => '081234567890',
            'delivery_address' => 'Jalan Roti Nomor 1',
            'delivery_fee' => '0',
            'total' => '1',
        ], $overrides);
    }
}
