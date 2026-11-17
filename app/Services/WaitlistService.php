<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\WaitlistStatus;
use App\Models\Appointment;
use App\Models\WaitlistEntry;
use RuntimeException;

class WaitlistService
{
    public function __construct(
        private ModuleManager $modules,
        private ReminderDispatcher $reminders,
    ) {}

    public function offerNext(Appointment $freedSlot): ?WaitlistEntry
    {
        if (! $this->modules->enabled('waitlist')) {
            return null;
        }

        $entry = WaitlistEntry::query()
            ->where('status', WaitlistStatus::Waiting)
            ->when($freedSlot->service_id, fn ($q) => $q->where(function ($inner) use ($freedSlot) {
                $inner->whereNull('service_id')->orWhere('service_id', $freedSlot->service_id);
            }))
            ->when($freedSlot->provider_id, fn ($q) => $q->where(function ($inner) use ($freedSlot) {
                $inner->whereNull('provider_id')->orWhere('provider_id', $freedSlot->provider_id);
            }))
            ->orderByDesc('priority')
            ->orderBy('created_at')
            ->first();

        if ($entry === null) {
            return null;
        }

        $entry->update([
            'status' => WaitlistStatus::Offered,
            'offered_appointment_id' => $freedSlot->id,
            'offered_at' => now(),
            'expires_at' => now()->addHours(4),
        ]);

        return $entry->fresh();
    }

    public function accept(WaitlistEntry $entry): Appointment
    {
        if (! $this->modules->enabled('waitlist')) {
            throw new RuntimeException('Waitlist module is disabled.');
        }

        if ($entry->offered_appointment_id === null) {
            throw new RuntimeException('No offered appointment on waitlist entry.');
        }

        $appointment = Appointment::query()->findOrFail($entry->offered_appointment_id);
        $appointment->update([
            'client_id' => $entry->client_id,
            'status' => AppointmentStatus::Confirmed,
            'confirmed_at' => now(),
            'cancelled_at' => null,
        ]);

        $entry->update(['status' => WaitlistStatus::Accepted]);

        if ($this->modules->enabled('reminders')) {
            $this->reminders->scheduleFor($appointment);
        }

        return $appointment->fresh();
    }
}
