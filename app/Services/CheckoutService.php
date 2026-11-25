<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        private MidtransGateway $midtrans,
        private TicketPricingService $ticketPricing,
        private PromoCodeService $promoCodes,
    ) {}

    public function startGeneralBooking(
        Ticket $ticket,
        User $user,
        string $visitDate,
        int $quantity,
        ?string $redeemCode = null,
    ): Payment {
        return DB::transaction(function () use ($ticket, $user, $visitDate, $quantity, $redeemCode): Payment {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($ticket->id);
            $this->ensureActiveTicket($ticket, 'general');

            $promoCode = $this->lockRedeemCode($redeemCode);
            $pricing = $this->ticketPricing->quote($ticket, $quantity, $promoCode);
            $this->reserveQuota($ticket, $visitDate, $quantity);

            $registration = Registration::query()->create([
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
                'registration_code' => 'TNR-'.Str::upper(Str::random(20)),
                'visit_date' => $visitDate,
                'quantity' => $quantity,
                'total_price' => $pricing['gross_amount'],
                'status' => 'awaiting_payment',
                'registered_at' => now(),
            ]);

            return $this->createPayment(
                $registration,
                $ticket,
                $user,
                $pricing,
                $quantity,
                $promoCode,
            );
        }, attempts: 3);
    }

    /**
     * @param  array{name: string, contact_person: string, visit_date: string, quantity: int}  $details
     */
    public function createSchoolApplication(
        Ticket $ticket,
        User $user,
        array $details,
    ): Registration {
        return DB::transaction(function () use ($ticket, $user, $details): Registration {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($ticket->id);
            $this->ensureActiveTicket($ticket, 'school');

            return Registration::query()->create([
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
                'registration_code' => 'TNR-'.Str::upper(Str::random(20)),
                'visit_date' => $details['visit_date'],
                'school_name' => $details['name'],
                'contact_person' => $details['contact_person'],
                'quantity' => $details['quantity'],
                'total_price' => (int) $ticket->price,
                'status' => 'pending_approval',
                'registered_at' => now(),
            ]);
        });
    }

    public function checkoutSchool(
        Registration $registration,
        User $user,
        ?string $redeemCode = null,
    ): Payment {
        return DB::transaction(function () use ($registration, $user, $redeemCode): Payment {
            $registration = Registration::query()
                ->lockForUpdate()
                ->findOrFail($registration->id);

            abort_unless($registration->user_id === $user->id, 403);

            $ticket = Ticket::query()
                ->lockForUpdate()
                ->findOrFail($registration->ticket_id);

            $this->ensureActiveTicket($ticket, 'school');

            if ($registration->status === 'awaiting_payment') {
                $pendingPayment = $registration->payments()
                    ->where('transaction_status', 'pending')
                    ->whereNotNull('snap_url')
                    ->latest()
                    ->first();

                if ($pendingPayment) {
                    return $pendingPayment;
                }
            }

            if ($registration->status !== 'approved') {
                throw ValidationException::withMessages([
                    'registration' => 'Pengajuan paket sekolah belum disetujui atau tidak dapat dibayar.',
                ]);
            }

            $promoCode = $this->lockRedeemCode($redeemCode);
            $pricing = $this->ticketPricing->quote($ticket, 1, $promoCode);

            $this->reserveQuota($ticket, $registration->visit_date->toDateString(), $registration->quantity);
            $registration->update([
                'status' => 'awaiting_payment',
                'total_price' => $pricing['gross_amount'],
            ]);

            return $this->createPayment($registration, $ticket, $user, $pricing, 1, $promoCode);
        }, attempts: 3);
    }

    /**
     * @param  array<string, mixed>  $notification
     */
    public function syncNotification(array $notification): void
    {
        if (! $this->midtrans->verifyNotificationSignature($notification)) {
            abort(403, 'Signature notifikasi Midtrans tidak valid.');
        }

        $orderId = $notification['order_id'] ?? null;

        if (! is_string($orderId) || $orderId === '') {
            abort(422, 'Order ID notifikasi tidak valid.');
        }

        $status = $this->midtrans->transactionStatus($orderId);
        $this->applyTransactionStatus($orderId, $status);
    }

    public function refreshStatus(Registration $registration, User $user): void
    {
        abort_unless($registration->user_id === $user->id, 403);

        $payment = $registration->payments()->latest()->firstOrFail();
        $status = $this->midtrans->transactionStatus($payment->order_id);

        $this->applyTransactionStatus($payment->order_id, $status);
    }

    /**
     * @param  array{
     *     normal_unit_amount: int,
     *     unit_amount: int,
     *     subtotal_amount: int,
     *     discount_type: ?string,
     *     discount_value: ?string,
     *     discount_amount: int,
     *     gross_amount: int,
     *     is_discounted: bool
     * }  $pricing
     */
    private function createPayment(
        Registration $registration,
        Ticket $ticket,
        User $user,
        array $pricing,
        int $itemQuantity,
        ?PromoCode $promoCode = null,
    ): Payment {
        $orderId = 'TNR-'.$registration->id.'-'.Str::upper(Str::random(8));

        $payment = Payment::query()->create([
            'registration_id' => $registration->id,
            'user_id' => $user->id,
            'order_id' => $orderId,
            'subtotal_amount' => $pricing['subtotal_amount'],
            'discount_type' => $pricing['discount_type'],
            'discount_value' => $pricing['discount_value'],
            'discount_amount' => $pricing['discount_amount'],
            'promo_code_id' => $promoCode?->id,
            'promo_code' => $promoCode?->code,
            'gross_amount' => $pricing['gross_amount'],
            'transaction_status' => 'pending',
        ]);

        try {
            $response = $this->midtrans->createSnapTransaction([
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $pricing['gross_amount'],
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
                'item_details' => [
                    [
                        'id' => (string) $ticket->id,
                        'price' => $pricing['unit_amount'],
                        'quantity' => $itemQuantity,
                        'name' => Str::limit($ticket->name, 50, ''),
                    ],
                ],
            ]);
        } catch (\Exception $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'payment' => 'Pembayaran belum dapat dibuat. Silakan coba kembali.',
            ]);
        }

        if (
            ! is_string($response->redirect_url ?? null) ||
            $response->redirect_url === '' ||
            ! is_string($response->token ?? null) ||
            $response->token === ''
        ) {
            throw new RuntimeException('Midtrans tidak memberikan URL atau token pembayaran.');
        }

        $payment->update([
            'snap_token' => $response->token,
            'snap_url' => $response->redirect_url,
        ]);

        if ($promoCode) {
            $this->promoCodes->reserveForPayment($promoCode, $payment);
        }

        return $payment;
    }

    private function lockRedeemCode(?string $code): ?PromoCode
    {
        if ($code === null || trim($code) === '') {
            return null;
        }

        return $this->promoCodes->lockForCheckout($code);
    }

    private function reserveQuota(Ticket $ticket, string $visitDate, int $quantity): void
    {
        DB::table('ticket_date_quotas')->insertOrIgnore([
            'ticket_id' => $ticket->id,
            'visit_date' => $visitDate,
            'quota' => $ticket->quota,
            'available_quota' => $ticket->quota,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $quota = DB::table('ticket_date_quotas')
            ->where('ticket_id', $ticket->id)
            ->where('visit_date', $visitDate)
            ->lockForUpdate()
            ->first();

        if (! $quota || $quota->available_quota < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Kuota pada tanggal yang dipilih tidak mencukupi.',
            ]);
        }

        DB::table('ticket_date_quotas')
            ->where('id', $quota->id)
            ->decrement('available_quota', $quantity);
    }

    private function applyTransactionStatus(string $orderId, object $status): void
    {
        DB::transaction(function () use ($orderId, $status): void {
            $payment = Payment::query()
                ->where('order_id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            $registration = Registration::query()
                ->lockForUpdate()
                ->findOrFail($payment->registration_id);
            $ticket = Ticket::query()
                ->lockForUpdate()
                ->findOrFail($registration->ticket_id);

            if (
                ($status->order_id ?? null) !== $payment->order_id ||
                ! $this->amountsMatch($payment->gross_amount, $status->gross_amount ?? null)
            ) {
                throw new RuntimeException('Order ID atau nominal transaksi Midtrans tidak cocok.');
            }

            $transactionStatus = (string) ($status->transaction_status ?? '');
            $fraudStatus = (string) ($status->fraud_status ?? '');
            $statusCode = (string) ($status->status_code ?? '');

            if ($payment->paid_at) {
                return;
            }

            $payment->update([
                'transaction_id' => $status->transaction_id ?? null,
                'payment_type' => $status->payment_type ?? null,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus ?: null,
            ]);

            if (
                $statusCode === '200' &&
                (
                    $transactionStatus === 'settlement' ||
                    ($transactionStatus === 'capture' && $fraudStatus === 'accept')
                )
            ) {
                if (in_array($registration->status, ['expired', 'cancelled'], true)) {
                    $this->reserveQuota(
                        $ticket,
                        $registration->visit_date->toDateString(),
                        $registration->quantity,
                    );
                } elseif ($registration->status !== 'awaiting_payment') {
                    throw new RuntimeException('Status pesanan tidak mengizinkan penerbitan e-ticket.');
                }

                $registration->update([
                    'status' => 'confirmed',
                    'e_ticket_code' => $registration->e_ticket_code ?: Str::upper(Str::random(20)),
                    'issued_at' => $registration->issued_at ?: now(),
                ]);

                $payment->update(['paid_at' => now()]);
                $this->promoCodes->redeemForPayment($payment);

                return;
            }

            if (
                in_array($transactionStatus, ['expire', 'cancel', 'deny'], true) &&
                $registration->status === 'awaiting_payment'
            ) {
                $this->releaseQuota($ticket, $registration);
                $this->promoCodes->releaseForPayment($payment);

                $registration->update([
                    'status' => $ticket->type === 'school'
                        ? 'approved'
                        : ($transactionStatus === 'cancel' ? 'cancelled' : 'expired'),
                ]);
            }
        }, attempts: 3);
    }

    private function releaseQuota(Ticket $ticket, Registration $registration): void
    {
        $quota = DB::table('ticket_date_quotas')
            ->where('ticket_id', $ticket->id)
            ->where('visit_date', $registration->visit_date->toDateString())
            ->lockForUpdate()
            ->first();

        if (! $quota || $quota->available_quota + $registration->quantity > $quota->quota) {
            throw new RuntimeException('Saldo kuota tidak konsisten saat membatalkan pesanan.');
        }

        DB::table('ticket_date_quotas')
            ->where('id', $quota->id)
            ->increment('available_quota', $registration->quantity);
    }

    private function ensureActiveTicket(Ticket $ticket, string $type): void
    {
        if (! $ticket->is_active || $ticket->type !== $type) {
            abort(404);
        }
    }

    private function amountsMatch(string $expectedAmount, mixed $receivedAmount): bool
    {
        if (! is_string($receivedAmount) && ! is_numeric($receivedAmount)) {
            return false;
        }

        return number_format((float) $expectedAmount, 2, '.', '') ===
            number_format((float) $receivedAmount, 2, '.', '');
    }
}
