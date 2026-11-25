<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function store(
        Request $request,
        Ticket $ticket,
        CheckoutService $checkoutService,
    ): RedirectResponse {
        abort_unless(
            $ticket->is_active && in_array($ticket->type, ['general', 'school'], true),
            404,
        );

        if ($ticket->type === 'school') {
            $data = $request->validate([
                'school_name' => ['required', 'string', 'max:255'],
                'contact_person' => ['required', 'string', 'max:255'],
                'visit_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
                'quantity' => ['required', 'integer', 'min:1', 'max:'.$ticket->quota],
            ]);

            $availableQuota = DB::table('ticket_date_quotas')
                ->where('ticket_id', $ticket->id)
                ->where('visit_date', $data['visit_date'])
                ->value('available_quota');

            if ($availableQuota !== null && $data['quantity'] > $availableQuota) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah peserta melebihi sisa kuota pada tanggal tersebut.',
                ]);
            }

            $checkoutService->createSchoolApplication($ticket, $request->user(), [
                'name' => $data['school_name'],
                'contact_person' => $data['contact_person'],
                'visit_date' => $data['visit_date'],
                'quantity' => (int) $data['quantity'],
            ]);

            return to_route('dashboard')
                ->with('success', 'Pengajuan paket sekolah sudah dikirim untuk ditinjau admin.');
        }

        $data = $request->validate([
            'visit_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$ticket->quota],
            'redeem_code' => ['nullable', 'string', 'max:32'],
        ]);

        $payment = $checkoutService->startGeneralBooking(
            $ticket,
            $request->user(),
            $data['visit_date'],
            (int) $data['quantity'],
            $data['redeem_code'] ?? null,
        );

        return redirect()->away($payment->snap_url);
    }
}
