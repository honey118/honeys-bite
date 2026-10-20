<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryFeeSettingsController extends Controller
{
    private const MAX_DELIVERY_FEE = '99999999.99';

    public function edit(): View
    {
        return view('admin.settings.delivery-fee', [
            'deliveryFee' => AppSetting::deliveryFee(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_fee' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.self::MAX_DELIVERY_FEE],
        ], [
            'delivery_fee.required' => 'Nominal ongkir wajib diisi.',
            'delivery_fee.numeric' => 'Nominal ongkir harus berupa angka.',
            'delivery_fee.decimal' => 'Nominal ongkir maksimal menggunakan dua angka di belakang koma.',
            'delivery_fee.min' => 'Nominal ongkir tidak boleh kurang dari Rp0.',
            'delivery_fee.max' => 'Nominal ongkir maksimal adalah Rp99.999.999,99.',
        ]);

        AppSetting::query()->updateOrCreate(
            ['key' => AppSetting::DELIVERY_FEE_KEY],
            ['value' => $validated['delivery_fee']],
        );

        return redirect()->route('admin.settings.delivery-fee.edit')
            ->with('success', 'Pengaturan ongkir berhasil diperbarui.');
    }
}
