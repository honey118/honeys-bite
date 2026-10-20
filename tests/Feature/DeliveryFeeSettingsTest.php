<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryFeeSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_view_or_update_delivery_fee_settings(): void
    {
        $this->get(route('admin.settings.delivery-fee.edit'))
            ->assertRedirect(route('login'));
        $this->put(route('admin.settings.delivery-fee.update'), ['delivery_fee' => '7000'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('app_settings', 0);
    }

    public function test_regular_users_cannot_view_or_update_delivery_fee_settings(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.settings.delivery-fee.edit'))->assertForbidden();
        $this->put(route('admin.settings.delivery-fee.update'), ['delivery_fee' => '7000'])
            ->assertForbidden();

        $this->assertDatabaseCount('app_settings', 0);
    }

    public function test_admin_can_view_default_and_persist_a_delivery_fee_update(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('admin.settings.delivery-fee.edit'))
            ->assertOk()
            ->assertSee('Rp5.000,00')
            ->assertSee('value="5000.00"', false);

        $this->put(route('admin.settings.delivery-fee.update'), ['delivery_fee' => '7250.50'])
            ->assertRedirect(route('admin.settings.delivery-fee.edit'))
            ->assertSessionHas('success', 'Pengaturan ongkir berhasil diperbarui.');

        $this->assertDatabaseHas('app_settings', [
            'key' => AppSetting::DELIVERY_FEE_KEY,
            'value' => '7250.50',
        ]);

        $this->get(route('admin.settings.delivery-fee.edit'))
            ->assertOk()
            ->assertSee('Rp7.250,50')
            ->assertSee('value="7250.50"', false);
    }

    public function test_admin_cannot_save_invalid_delivery_fee_values(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        foreach ([
            ['value' => '-1', 'message' => 'Nominal ongkir tidak boleh kurang dari Rp0.'],
            ['value' => 'bukan-angka', 'message' => 'Nominal ongkir harus berupa angka.'],
            ['value' => '100000000', 'message' => 'Nominal ongkir maksimal adalah Rp99.999.999,99.'],
        ] as $invalidFee) {
            $this->from(route('admin.settings.delivery-fee.edit'))
                ->followingRedirects()
                ->put(route('admin.settings.delivery-fee.update'), ['delivery_fee' => $invalidFee['value']])
                ->assertOk()
                ->assertSee($invalidFee['message']);
        }

        $this->assertDatabaseCount('app_settings', 0);
    }
}
