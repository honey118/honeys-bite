<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'order_number',
    'subtotal',
    'delivery_fee',
    'total',
    'fulfillment_method',
    'payment_method',
    'payment_status',
    'recipient_name',
    'recipient_phone',
    'delivery_address',
    'status',
    'rejection_reason',
    'stock_allocated',
    'stock_restored',
])]
class Order extends Model
{
    public const STATUS_PENDING = 'Menunggu Konfirmasi';

    public const STATUS_AWAITING_PAYMENT = 'Menunggu Pembayaran';

    public const STATUS_IN_TRANSIT = 'Dalam Perjalanan';

    public const STATUS_READY_FOR_PICKUP = 'Silakan Ambil di Toko';

    public const STATUS_REJECTED = 'Ditolak';

    public const STATUS_COMPLETED = 'Selesai';

    public const PAYMENT_UNPAID = 'Belum Dibayar';

    public const PAYMENT_PAID = 'Dibayar';

    public const PAYMENT_DEMO_PAID = 'Dibayar (Demo)';

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'stock_allocated' => 'boolean',
            'stock_restored' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function item(): HasOne
    {
        return $this->hasOne(OrderItem::class);
    }
}
