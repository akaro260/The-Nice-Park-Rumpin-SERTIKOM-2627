<?php

namespace App\Services;

use App\Models\PromoCode;
use App\Models\Ticket;
use Illuminate\Validation\ValidationException;
use LogicException;

class TicketPricingService
{
    private const MAX_AMOUNT = 9_999_999_999;

    /**
     * @return array{
     *     normal_unit_amount: int,
     *     unit_amount: int,
     *     subtotal_amount: int,
     *     discount_type: ?string,
     *     discount_value: ?string,
     *     discount_amount: int,
     *     gross_amount: int,
     *     is_discounted: bool
     * }
     */
    public function quote(
        Ticket $ticket,
        int $quantity = 1,
        ?PromoCode $promoCode = null,
    ): array {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah produk harus lebih dari nol.',
            ]);
        }

        $normalUnitAmount = (int) $ticket->price;

        if ($normalUnitAmount < 1) {
            throw ValidationException::withMessages([
                'ticket' => 'Harga produk tidak valid untuk pembayaran.',
            ]);
        }

        if ($normalUnitAmount > intdiv(self::MAX_AMOUNT, $quantity)) {
            throw ValidationException::withMessages([
                'quantity' => 'Total pembayaran melebihi batas transaksi.',
            ]);
        }

        $subtotalAmount = $normalUnitAmount * $quantity;
        $unitAmount = $normalUnitAmount;
        $discountType = null;
        $discountValue = null;

        if ($promoCode) {
            $discountType = $promoCode->discount_type;
            $discountValue = $promoCode->discount_value;
            $unitAmount = $this->discountedUnitAmount(
                $normalUnitAmount,
                $discountType,
                $discountValue,
                'redeem_code',
            );

            if ($unitAmount >= $normalUnitAmount) {
                throw ValidationException::withMessages([
                    'redeem_code' => 'Kode redeem tidak mengurangi harga produk ini.',
                ]);
            }
        } elseif ($ticket->discount_is_active) {
            $type = $ticket->discount_type;
            $value = $ticket->discount_value;
            $startsOn = $ticket->discount_starts_on?->toDateString();
            $endsOn = $ticket->discount_ends_on?->toDateString();

            if (
                ! in_array($type, ['percentage', 'fixed'], true) ||
                $value === null ||
                $startsOn === null ||
                $endsOn === null ||
                $startsOn > $endsOn
            ) {
                throw new LogicException('Konfigurasi diskon produk tidak valid.');
            }

            $discountedUnitAmount = $this->discountedUnitAmount(
                $normalUnitAmount,
                $type,
                $value,
            );
            $today = now()->toDateString();

            if ($today >= $startsOn && $today <= $endsOn) {
                $unitAmount = $discountedUnitAmount;
                $discountType = $type;
                $discountValue = $value;
            }
        }

        if ($unitAmount > intdiv(self::MAX_AMOUNT, $quantity)) {
            throw ValidationException::withMessages([
                'quantity' => 'Total pembayaran melebihi batas transaksi.',
            ]);
        }

        $grossAmount = $unitAmount * $quantity;
        $discountAmount = $subtotalAmount - $grossAmount;

        return [
            'normal_unit_amount' => $normalUnitAmount,
            'unit_amount' => $unitAmount,
            'subtotal_amount' => $subtotalAmount,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'gross_amount' => $grossAmount,
            'is_discounted' => $discountAmount > 0,
        ];
    }

    private function discountedUnitAmount(
        int $normalAmount,
        string $type,
        string $value,
        ?string $validationField = null,
    ): int {
        $valueAsNumber = (float) $value;

        if ($type === 'percentage') {
            if (
                $valueAsNumber <= 0 ||
                $valueAsNumber >= 100 ||
                floor($valueAsNumber) !== $valueAsNumber
            ) {
                if ($validationField) {
                    throw ValidationException::withMessages([
                        $validationField => 'Nilai kode redeem tidak valid untuk produk ini.',
                    ]);
                }

                throw new LogicException('Persentase diskon harus berupa bilangan bulat antara 1 dan 99.');
            }

            $discountedAmount = intdiv(
                $normalAmount * (100 - (int) $valueAsNumber) + 50,
                100,
            );
        } elseif ($type === 'fixed') {
            if (
                $valueAsNumber <= 0 ||
                floor($valueAsNumber) !== $valueAsNumber ||
                $valueAsNumber >= $normalAmount
            ) {
                if ($validationField) {
                    throw ValidationException::withMessages([
                        $validationField => 'Kode redeem tidak berlaku untuk harga produk ini.',
                    ]);
                }

                throw new LogicException('Nominal diskon harus berupa bilangan bulat di bawah harga normal.');
            }

            $discountedAmount = $normalAmount - (int) $valueAsNumber;
        } else {
            if ($validationField) {
                throw ValidationException::withMessages([
                    $validationField => 'Jenis diskon pada kode redeem tidak valid.',
                ]);
            }

            throw new LogicException('Jenis diskon tidak valid.');
        }

        if ($discountedAmount < 1) {
            if ($validationField) {
                throw ValidationException::withMessages([
                    $validationField => 'Kode redeem harus menyisakan harga minimal Rp1.',
                ]);
            }

            throw new LogicException('Diskon harus menyisakan harga minimal Rp1.');
        }

        return $discountedAmount;
    }
}
