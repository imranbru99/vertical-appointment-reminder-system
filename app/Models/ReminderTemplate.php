<?php

namespace App\Models;

use App\Enums\ReminderChannel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'channel', 'subject', 'body', 'hours_before', 'is_active'])]
class ReminderTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'channel' => ReminderChannel::class,
            'hours_before' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}