<?php

namespace App\Models;

use App\Enums\ReminderChannel;
use App\Enums\ReminderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'appointment_id',
    'channel',
    'status',
    'template_key',
    'scheduled_for',
    'sent_at',
    'recipient',
    'body',
    'provider_response',
])]
class Reminder extends Model
{
    protected function casts(): array
    {
        return [
            'channel' => ReminderChannel::class,
            'status' => ReminderStatus::class,
            'scheduled_for' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}