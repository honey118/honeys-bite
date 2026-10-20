<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value'])]
class AppSetting extends Model
{
    public const DELIVERY_FEE_KEY = 'delivery_fee';

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    public static function deliveryFee(): string
    {
        return (string) (static::query()
            ->where('key', self::DELIVERY_FEE_KEY)
            ->value('value') ?? config('orders.delivery_fee', '5000.00'));
    }
}
