<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'code',
    'discount_type',
    'discount_value',
    'is_active',
    'reserved_payment_id',
    'redeemed_payment_id',
    'redeemed_by_user_id',
    'redeemed_at',
])]
class PromoCode extends Model
{
    public function reservedPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'reserved_payment_id');
    }

    public function redeemedPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'redeemed_payment_id');
    }

    public function redeemedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'is_active' => 'boolean',
            'reserved_payment_id' => 'integer',
            'redeemed_payment_id' => 'integer',
            'redeemed_by_user_id' => 'integer',
            'redeemed_at' => 'datetime',
        ];
    }
}
