<?php

namespace App\Http\Controllers\costumer;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    //
        public function index()
    {
        $workshops = Ticket::where('status', 'published')
            ->with(['registrations' => fn($q) => $q->where('user_id', auth()->id())])
            ->latest()
            ->paginate(9);

        return view('peserta.workshops.index', compact('workshops'));
    }
}
