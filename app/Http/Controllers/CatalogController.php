<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\TicketPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(TicketPricingService $ticketPricingService): View
    {
        $generalTickets = Ticket::query()
            ->where('is_active', true)
            ->where('type', 'general')
            ->orderBy('name')
            ->get();

        $schoolPackages = Ticket::query()
            ->where('is_active', true)
            ->where('type', 'school')
            ->orderBy('name')
            ->get();

        $ticketPricing = $generalTickets
            ->concat($schoolPackages)
            ->mapWithKeys(fn (Ticket $ticket): array => [
                $ticket->id => $ticketPricingService->quote($ticket),
            ])
            ->all();

        return view('welcome', compact('generalTickets', 'schoolPackages', 'ticketPricing'));
    }

    public function show(
        Request $request,
        Ticket $ticket,
        TicketPricingService $ticketPricingService,
    ): View {
        abort_unless($ticket->is_active && in_array($ticket->type, ['general', 'school'], true), 404);

        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $visitDate = $data['date'] ?? null;
        $availableQuota = $ticket->quota;

        if ($visitDate) {
            $dateQuota = DB::table('ticket_date_quotas')
                ->where('ticket_id', $ticket->id)
                ->where('visit_date', $visitDate)
                ->value('available_quota');

            if ($dateQuota !== null) {
                $availableQuota = (int) $dateQuota;
            }
        }

        $pricing = $ticketPricingService->quote($ticket);

        return view('catalog.show', compact('ticket', 'visitDate', 'availableQuota', 'pricing'));
    }
}
