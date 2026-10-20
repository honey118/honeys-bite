<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_admin_can_create_view_update_and_archive_a_product(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.products.index'))->assertOk();
        $this->get(route('admin.products.create'))->assertOk();

        $this->post(route('admin.products.store'), [
            'name' => 'Roti Madu',
            'description' => 'Roti lembut dengan madu.',
            'price' => '12500.50',
            'stock' => '8',
            'is_active' => '1',
            'photo' => UploadedFile::fake()->image('roti.jpg'),
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Roti Madu')->firstOrFail();
        $this->assertSame('12500.50', $product->price);
        $this->assertSame(8, $product->stock);
        $this->assertTrue($product->is_active);
        $this->assertTrue(Storage::disk('public')->exists($product->photo_path));

        $this->get(route('admin.products.show', $product))->assertOk()->assertSee('Roti Madu');
        $this->get(route('admin.products.edit', $product))->assertOk();

        $oldPhotoPath = $product->photo_path;
        $this->put(route('admin.products.update', $product), [
            'name' => 'Roti Madu Spesial',
            'description' => 'Resep terbaru.',
            'price' => '15000.00',
            'stock' => '5',
            'is_active' => '1',
            'photo' => UploadedFile::fake()->image('roti-baru.png'),
        ])->assertRedirect(route('admin.products.show', $product));

        $product->refresh();
        $this->assertSame('Roti Madu Spesial', $product->name);
        $this->assertSame('15000.00', $product->price);
        $this->assertSame(5, $product->stock);
        $this->assertFalse(Storage::disk('public')->exists($oldPhotoPath));
        $this->assertTrue(Storage::disk('public')->exists($product->photo_path));

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);
    }

    public function test_regular_user_cannot_access_admin_product_routes(): void
    {
        $product = Product::create([
            'name' => 'Roti Uji',
            'description' => null,
            'price' => '1000.00',
            'stock' => 1,
            'is_active' => true,
        ]);

        $this->actingAs(User::factory()->create());

        $this->get(route('admin.products.index'))->assertForbidden();
        $this->post(route('admin.products.store'), [])->assertForbidden();
        $this->get(route('admin.products.edit', $product))->assertForbidden();
    }

    public function test_product_price_and_stock_are_validated_server_side(): void
    {
        $this->actingAs($this->admin());

        foreach ([['-1', '3'], ['100000000', '3'], ['12.345', '3'], ['12.00', '-1'], ['12.00', '1.5']] as [$price, $stock]) {
            $this->from(route('admin.products.create'))
                ->post(route('admin.products.store'), [
                    'name' => 'Produk Tidak Valid',
                    'description' => '',
                    'price' => $price,
                    'stock' => $stock,
                    'is_active' => '1',
                ])
                ->assertRedirect(route('admin.products.create'))
                ->assertSessionHasErrors(array_values(array_filter([
                    is_numeric($price) && (float) $price >= 0 && (float) $price <= 99999999.99 && preg_match('/^\d+(\.\d{1,2})?$/', $price) ? null : 'price',
                    ctype_digit($stock) ? null : 'stock',
                ])));
        }

        $this->assertDatabaseCount('products', 0);
    }

    public function test_catalog_lists_active_products_and_hides_archived_products(): void
    {
        Product::create([
            'name' => 'Roti Aktif',
            'description' => 'Aroma mentega.',
            'price' => '7500.00',
            'stock' => 2,
            'is_active' => true,
        ]);
        Product::create([
            'name' => 'Roti Arsip',
            'description' => 'Tidak lagi dijual.',
            'price' => '6500.00',
            'stock' => 0,
            'is_active' => false,
        ]);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Roti Aktif')
            ->assertSee('Tersedia · 2 stok')
            ->assertDontSee('Roti Arsip');
    }

    public function test_catalog_shows_out_of_stock_status_without_exposing_archived_products(): void
    {
        $product = Product::create([
            'name' => 'Roti Habis',
            'description' => null,
            'price' => '5000.00',
            'stock' => 0,
            'is_active' => true,
        ]);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Roti Habis')
            ->assertSee('Stok habis')
            ->assertDontSee(route('checkout.create', $product), false);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Stok habis · tidak menerima pesanan baru')
            ->assertDontSee(route('checkout.create', $product), false);
    }

    public function test_invalid_photo_upload_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => 'Produk Upload Salah',
                'description' => '',
                'price' => '1000.00',
                'stock' => '1',
                'is_active' => '1',
                'photo' => UploadedFile::fake()->create('bad.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors(['photo']);

        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('products'));
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();

        return $admin;
    }
}
