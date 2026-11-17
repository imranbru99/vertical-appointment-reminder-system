<?php

namespace App\Enums;

enum ReminderChannel: string
{
    case Sms = 'sms';
    case Email = 'email';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::Sms => 'SMS',
            self::Email => 'Email',
            self::Both => 'SMS + Email',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $channel) => [$channel->value => $channel->label()])
            ->all();
    }
}
