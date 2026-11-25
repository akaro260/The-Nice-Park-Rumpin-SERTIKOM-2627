<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;

class MidtransGateway
{
    public function createSnapTransaction(array $parameters): object
    {
        $this->configure();

        return Snap::createTransaction($parameters);
    }

    public function transactionStatus(string $orderId): object
    {
        $this->configure();

        return Transaction::status($orderId);
    }

    /**
     * @param  array<string, mixed>  $notification
     */
    public function verifyNotificationSignature(array $notification): bool
    {
        $signatureKey = config('services.midtrans.signature_key');
        $orderId = $notification['order_id'] ?? null;
        $statusCode = $notification['status_code'] ?? null;
        $grossAmount = $notification['gross_amount'] ?? null;
        $receivedSignature = $notification['signature_key'] ?? null;

        if (
            ! is_string($signatureKey) ||
            $signatureKey === '' ||
            ! is_string($orderId) ||
            ! is_string($statusCode) ||
            ! is_string($grossAmount) ||
            ! is_string($receivedSignature)
        ) {
            return false;
        }

        $expectedSignature = hash(
            'sha512',
            $orderId.$statusCode.$grossAmount.$signatureKey,
        );

        return hash_equals($expectedSignature, $receivedSignature);
    }

    private function configure(): void
    {
        $serverKey = config('services.midtrans.server_key');
        $isProduction = (bool) config('services.midtrans.is_production', false);
        $expectedPrefix = $isProduction ? 'Mid-server-' : 'SB-Mid-server-';

        if (
            ! is_string($serverKey) ||
            ! str_starts_with($serverKey, $expectedPrefix)
        ) {
            throw new RuntimeException(
                'Server Key Midtrans tidak sesuai dengan environment.',
            );
        }

        Config::$serverKey = $serverKey;
        Config::$isProduction = $isProduction;
    }
}
