<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'ticket_id', 'registration_code', 'quantity', 'total_price', 'status', 'visit_date', 'school_name', 'contact_person', 'rejection_reason', 'e_ticket_code', 'registered_at', 'issued_at'])]
class Registration extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'total_price' => 'decimal:2',
            'visit_date' => 'date',
            'registered_at' => 'datetime',
            'issued_at' => 'datetime',
        ];
    }
}
