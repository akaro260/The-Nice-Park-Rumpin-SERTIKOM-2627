<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['registration_id', 'user_id', 'payment_gateway', 'transaction_id', 'order_id', 'payment_type', 'gross_amount', 'subtotal_amount', 'discount_type', 'discount_value', 'discount_amount', 'promo_code_id', 'promo_code', 'transaction_status', 'fraud_status', 'snap_token', 'snap_url', 'paid_at', 'expired_at'])]
class Payment extends Model
{
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'subtotal_amount' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'promo_code_id' => 'integer',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }
}
