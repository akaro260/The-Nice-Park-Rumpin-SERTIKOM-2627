<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'description', 'price', 'quota', 'available_quota', 'is_active', 'discount_type', 'discount_value', 'discount_starts_on', 'discount_ends_on', 'discount_is_active'])]
class Ticket extends Model
{
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quota' => 'integer',
            'available_quota' => 'integer',
            'is_active' => 'boolean',
            'discount_value' => 'decimal:2',
            'discount_starts_on' => 'date',
            'discount_ends_on' => 'date',
            'discount_is_active' => 'boolean',
        ];
    }
}
