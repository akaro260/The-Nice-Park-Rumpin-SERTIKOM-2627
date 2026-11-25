<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function checkout(
        Registration $registration,
        Request $request,
        CheckoutService $checkoutService,
    ): RedirectResponse {
        abort_unless($registration->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'redeem_code' => ['nullable', 'string', 'max:32'],
        ]);
        $payment = $checkoutService->checkoutSchool(
            $registration,
            $request->user(),
            $data['redeem_code'] ?? null,
        );

        return redirect()->away($payment->snap_url);
    }

    public function checkStatus(
        Registration $registration,
        Request $request,
        CheckoutService $checkoutService,
    ): RedirectResponse {
        abort_unless($registration->user_id === $request->user()->id, 403);

        try {
            $checkoutService->refreshStatus($registration, $request->user());
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            report($exception);

            return to_route('dashboard')
                ->with('error', 'Status pembayaran belum dapat diperiksa. Coba kembali beberapa saat lagi.');
        }

        $registration->refresh();

        return to_route('dashboard')->with(
            $registration->status === 'confirmed' ? 'success' : 'info',
            $registration->status === 'confirmed'
                ? 'Pembayaran berhasil diverifikasi.'
                : 'Pembayaran masih menunggu penyelesaian.',
        );
    }

    public function notification(
        Request $request,
        CheckoutService $checkoutService,
    ): JsonResponse {
        if (! config('services.midtrans.signature_key')) {
            return response()->json([
                'message' => 'Signature Secret Key Midtrans belum dikonfigurasi.',
            ], 503);
        }

        $notification = $request->validate([
            'order_id' => ['required', 'string', 'max:50'],
            'status_code' => ['required', 'string', 'size:3'],
            'gross_amount' => ['required', 'string', 'regex:/^\d{1,10}(\.\d{1,2})?$/'],
            'signature_key' => ['required', 'string', 'size:128'],
        ]);

        $checkoutService->syncNotification($notification);

        return response()->json(['received' => true]);
    }
}
