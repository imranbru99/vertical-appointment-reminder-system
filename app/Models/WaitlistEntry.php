<?php

namespace App\Models;

use App\Enums\WaitlistStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'service_id',
    'provider_id',
    'offered_appointment_id',
    'status',
    'priority',
    'preferred_date',
    'preferred_time_window',
    'offered_at',
    'expires_at',
    'notes',
])]
class WaitlistEntry extends Model
{
    protected function casts(): array
    {
        return [
            'status' => WaitlistStatus::class,
            'priority' => 'integer',
            'preferred_date' => 'date',
            'offered_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function offeredAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'offered_appointment_id');
    }
}