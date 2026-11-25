<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PromoCode;
use Illuminate\Validation\ValidationException;
use LogicException;

class PromoCodeService
{
    public function lockForCheckout(string $code): PromoCode
    {
        $promoCode = PromoCode::query()
            ->where('code', mb_strtoupper(trim($code)))
            ->lockForUpdate()
            ->first();

        if (
            ! $promoCode ||
            ! $promoCode->is_active ||
            $promoCode->redeemed_at ||
            $promoCode->reserved_payment_id
        ) {
            throw ValidationException::withMessages([
                'redeem_code' => 'Kode redeem tidak tersedia. Periksa kode atau gunakan kode lain.',
            ]);
        }

        return $promoCode;
    }

    public function reserveForPayment(PromoCode $promoCode, Payment $payment): void
    {
        $promoCode->update(['reserved_payment_id' => $payment->id]);
    }

    public function redeemForPayment(Payment $payment): void
    {
        if (! $payment->promo_code_id) {
            return;
        }

        $promoCode = PromoCode::query()
            ->lockForUpdate()
            ->findOrFail($payment->promo_code_id);

        if ($promoCode->redeemed_payment_id === $payment->id) {
            return;
        }

        if (
            $promoCode->redeemed_at ||
            $promoCode->reserved_payment_id !== $payment->id
        ) {
            throw new LogicException('Status penggunaan kode redeem tidak konsisten.');
        }

        $promoCode->update([
            'reserved_payment_id' => null,
            'redeemed_payment_id' => $payment->id,
            'redeemed_by_user_id' => $payment->user_id,
            'redeemed_at' => now(),
        ]);
    }

    public function releaseForPayment(Payment $payment): void
    {
        if (! $payment->promo_code_id) {
            return;
        }

        $promoCode = PromoCode::query()
            ->lockForUpdate()
            ->findOrFail($payment->promo_code_id);

        if ($promoCode->reserved_payment_id === $payment->id) {
            $promoCode->update(['reserved_payment_id' => null]);
        }
    }
}
