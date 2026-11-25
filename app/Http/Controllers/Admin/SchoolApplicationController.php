<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SchoolApplicationController extends Controller
{
    public function index(): View
    {
        $applications = Registration::query()
            ->with(['ticket', 'user'])
            ->whereHas('ticket', fn ($query) => $query->where('type', 'school'))
            ->latest()
            ->paginate(15);

        return view('admin.applications.index', compact('applications'));
    }

    public function approve(Registration $registration): RedirectResponse
    {
        DB::transaction(function () use ($registration): void {
            $lockedRegistration = Registration::query()
                ->lockForUpdate()
                ->findOrFail($registration->id);
            $ticket = Ticket::query()
                ->lockForUpdate()
                ->findOrFail($lockedRegistration->ticket_id);

            abort_unless($ticket->type === 'school', 404);

            if ($lockedRegistration->status !== 'pending_approval') {
                throw ValidationException::withMessages([
                    'registration' => 'Pengajuan ini sudah diproses.',
                ]);
            }

            if (! $ticket->is_active) {
                throw ValidationException::withMessages([
                    'registration' => 'Paket sekolah sudah tidak aktif dan tidak dapat disetujui.',
                ]);
            }

            $lockedRegistration->update([
                'status' => 'approved',
                'total_price' => (int) $ticket->price,
            ]);
        });

        return to_route('admin.applications.index')
            ->with('success', 'Pengajuan sekolah disetujui.');
    }

    public function reject(Request $request, Registration $registration): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($registration, $data): void {
            $lockedRegistration = Registration::query()
                ->lockForUpdate()
                ->findOrFail($registration->id);
            $ticket = Ticket::query()
                ->lockForUpdate()
                ->findOrFail($lockedRegistration->ticket_id);

            abort_unless($ticket->type === 'school', 404);

            if ($lockedRegistration->status !== 'pending_approval') {
                throw ValidationException::withMessages([
                    'registration' => 'Pengajuan ini sudah diproses.',
                ]);
            }

            $lockedRegistration->update([
                'status' => 'rejected',
                'rejection_reason' => $data['rejection_reason'],
            ]);
        });

        return to_route('admin.applications.index')
            ->with('success', 'Pengajuan sekolah ditolak.');
    }
}
