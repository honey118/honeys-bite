<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_with_completed_order_can_review_its_product_without_changing_order_or_stock(): void
    {
        $customer = User::factory()->create();
        $product = $this->product(['stock' => 20]);
        $order = $this->order($customer, $product, Order::STATUS_COMPLETED, 2, true);
        $this->actingAs($customer);

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Beri rating dan ulasan');

        $this->post(route('products.reviews.store', $product), [
            'rating' => '5',
            'body' => 'Roti tetap enak dan segar.',
            'user_id' => User::factory()->create()->getKey(),
            'product_id' => $this->product()->getKey(),
        ])->assertRedirect(route('products.show', $product));

        $this->assertDatabaseHas('product_reviews', [
            'user_id' => $customer->getKey(),
            'product_id' => $product->getKey(),
            'rating' => 5,
            'body' => 'Roti tetap enak dan segar.',
        ]);
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertSame(Order::PAYMENT_UNPAID, $order->fresh()->payment_status);
        $this->assertTrue($order->fresh()->stock_allocated);
        $this->assertSame(18, $product->fresh()->stock);
    }

    public function test_review_is_rejected_until_customer_order_is_completed(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        $order = $this->order($customer, $product, Order::STATUS_IN_TRANSIT);
        $this->actingAs($customer);

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertDontSee('Beri rating dan ulasan');
        $this->from(route('orders.show', $order))
            ->followingRedirects()
            ->post(route('products.reviews.store', $product), $this->reviewPayload())
            ->assertOk()
            ->assertSee('Ulasan hanya dapat diberikan untuk produk dari pesanan Anda yang sudah selesai.');

        $this->assertDatabaseCount('product_reviews', 0);
        $this->assertSame(Order::STATUS_IN_TRANSIT, $order->fresh()->status);
    }

    public function test_customer_cannot_review_a_product_from_another_customers_completed_order(): void
    {
        $owner = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $product = $this->product();
        $this->order($owner, $product, Order::STATUS_COMPLETED);
        $this->actingAs($otherCustomer);

        $this->post(route('products.reviews.store', $product), $this->reviewPayload())
            ->assertSessionHasErrors(['review']);

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_admin_cannot_review_a_product_on_behalf_of_a_customer(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();
        $product = $this->product();
        $this->order($customer, $product, Order::STATUS_COMPLETED);
        $this->actingAs($admin);

        $this->post(route('products.reviews.store', $product), $this->reviewPayload())
            ->assertSessionHasErrors(['review']);

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_customer_cannot_review_a_product_missing_from_their_completed_order(): void
    {
        $customer = User::factory()->create();
        $orderedProduct = $this->product();
        $otherProduct = $this->product();
        $this->order($customer, $orderedProduct, Order::STATUS_COMPLETED);
        $this->actingAs($customer);

        $this->post(route('products.reviews.store', $otherProduct), $this->reviewPayload())
            ->assertSessionHasErrors(['review']);

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_product_name_snapshot_does_not_qualify_order_without_product_id(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        $order = $this->order($customer, $product, Order::STATUS_COMPLETED);
        $order->item()->update(['product_id' => null, 'product_name' => $product->name]);
        $this->actingAs($customer);

        $this->post(route('products.reviews.store', $product), $this->reviewPayload())
            ->assertSessionHasErrors(['review']);

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_guest_cannot_submit_a_product_review(): void
    {
        $product = $this->product();

        $this->post(route('products.reviews.store', $product), $this->reviewPayload())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_rating_must_be_an_integer_between_one_and_five(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        $order = $this->order($customer, $product, Order::STATUS_COMPLETED);
        $this->actingAs($customer);

        foreach (['0', '6', '2.5'] as $rating) {
            $this->post(route('products.reviews.store', $product), $this->reviewPayload(['rating' => $rating]))
                ->assertSessionHasErrors(['rating']);
        }

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_review_text_is_required_and_limited_to_one_thousand_characters(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        $this->order($customer, $product, Order::STATUS_COMPLETED);
        $this->actingAs($customer);

        $this->post(route('products.reviews.store', $product), $this->reviewPayload(['body' => '']))
            ->assertSessionHasErrors(['body']);
        $this->post(route('products.reviews.store', $product), $this->reviewPayload(['body' => str_repeat('a', 1001)]))
            ->assertSessionHasErrors(['body']);

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_customer_cannot_review_the_same_product_twice(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        $order = $this->order($customer, $product, Order::STATUS_COMPLETED);
        $this->actingAs($customer);

        $this->post(route('products.reviews.store', $product), $this->reviewPayload())
            ->assertRedirect(route('products.show', $product));
        $this->from(route('orders.show', $order))
            ->followingRedirects()
            ->post(route('products.reviews.store', $product), $this->reviewPayload())
            ->assertOk()
            ->assertSee('Anda sudah memberikan ulasan untuk produk ini.');

        $this->assertDatabaseCount('product_reviews', 1);
    }

    public function test_database_unique_constraint_prevents_duplicate_user_product_reviews(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        ProductReview::factory()->for($customer)->for($product)->create();

        $this->expectException(UniqueConstraintViolationException::class);

        ProductReview::factory()->for($customer)->for($product)->create();
    }

    public function test_archived_product_keeps_historical_reviews_visible(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        $this->order($customer, $product, Order::STATUS_COMPLETED);
        $this->actingAs($customer);

        $this->post(route('products.reviews.store', $product), $this->reviewPayload([
            'rating' => '4',
            'body' => '<script>alert(1)</script> Tetap tersimpan.',
        ]))->assertRedirect(route('products.show', $product));
        $product->update(['is_active' => false]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Produk diarsipkan')
            ->assertSee('Rating 4.0 dari 5 · 1 ulasan')
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->getKey(),
            'body' => '<script>alert(1)</script> Tetap tersimpan.',
        ]);
    }

    public function test_customer_order_detail_shows_review_form_only_for_completed_unreviewed_product(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        $completedOrder = $this->order($customer, $product, Order::STATUS_COMPLETED);
        $pendingOrder = $this->order($customer, $this->product(), Order::STATUS_PENDING);
        $this->actingAs($customer);

        $this->get(route('orders.show', $completedOrder))
            ->assertOk()
            ->assertSee('Beri rating dan ulasan');
        $this->get(route('orders.show', $pendingOrder))
            ->assertOk()
            ->assertDontSee('Beri rating dan ulasan');
    }

    private function product(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Roti Uji',
            'description' => 'Produk untuk pengujian ulasan.',
            'price' => '1000.00',
            'stock' => 20,
            'is_active' => true,
        ], $attributes));
    }

    private function order(
        User $customer,
        Product $product,
        string $status,
        int $quantity = 2,
        bool $stockAllocated = false,
    ): Order {
        $subtotal = number_format((float) $product->price * $quantity, 2, '.', '');
        $order = $customer->orders()->create([
            'order_number' => 'HB-'.strtoupper((string) str()->ulid()),
            'subtotal' => $subtotal,
            'delivery_fee' => '0.00',
            'total' => $subtotal,
            'fulfillment_method' => 'pickup',
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_UNPAID,
            'status' => $status,
            'stock_allocated' => $stockAllocated,
            'stock_restored' => false,
        ]);

        $order->item()->create([
            'product_id' => $product->getKey(),
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ]);

        if ($stockAllocated) {
            $product->decrement('stock', $quantity);
        }

        return $order;
    }

    private function reviewPayload(array $overrides = []): array
    {
        return array_merge([
            'rating' => '5',
            'body' => 'Roti enak dan segar.',
        ], $overrides);
    }
}
