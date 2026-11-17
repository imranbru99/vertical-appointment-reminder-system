<?php

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Enums\ReminderStatus;
use App\Models\Appointment;
use App\Models\Reminder;
use App\Enums\WaitlistStatus;
use App\Models\WaitlistEntry;
use App\Services\ModuleManager;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AppointmentStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $modules = app(ModuleManager::class);

        return [
            Stat::make('Today\'s Appointments', Appointment::query()->whereDate('starts_at', today())->count())
                ->description('Scheduled on calendar')
                ->color('primary'),
            Stat::make('Pending Reminders', Reminder::query()->where('status', ReminderStatus::Pending)->count())
                ->description($modules->enabled('reminders') ? 'Queued outreach' : 'Module off')
                ->color('warning'),
            Stat::make('Waitlist Open', WaitlistEntry::query()->where('status', WaitlistStatus::Waiting)->count())
                ->description($modules->enabled('waitlist') ? 'Clients waiting for slots' : 'Module off')
                ->color('success'),
            Stat::make('Hours Saved / Week', $modules->weeklyHoursSaved())
                ->description('Enabled module impact')
                ->color('danger'),
        ];
    }
}