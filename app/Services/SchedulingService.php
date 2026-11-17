<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AvailabilitySlot;
use App\Models\Provider;
use App\Models\Service;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class SchedulingService
{
    public function __construct(private ModuleManager $modules) {}

  /**
     * @return Collection<int, array{starts_at: Carbon, ends_at: Carbon}>
     */
    public function suggestSlots(
        Provider $provider,
        Service $service,
        CarbonInterface $from,
        CarbonInterface $to,
        int $limit = 12
    ): Collection {
        if (! $this->modules->enabled('scheduling')) {
            return collect();
        }

        $slots = collect();
        $cursor = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();
        $duration = $service->duration_minutes + $service->buffer_minutes;

        while ($cursor->lte($end) && $slots->count() < $limit) {
            $weekday = (int) $cursor->dayOfWeek;
            $windows = AvailabilitySlot::query()
                ->where('provider_id', $provider->id)
                ->where('weekday', $weekday)
                ->where('is_active', true)
                ->get();

            foreach ($windows as $window) {
                $windowStart = Carbon::parse($cursor->toDateString().' '.$window->starts_at);
                $windowEnd = Carbon::parse($cursor->toDateString().' '.$window->ends_at);
                $slotStart = $windowStart->copy();

                while ($slotStart->copy()->addMinutes($duration)->lte($windowEnd)) {
                    $slotEnd = $slotStart->copy()->addMinutes($service->duration_minutes);

                    if ($slotStart->gte($from) && $slotStart->lte($to)
                        && ! $this->hasConflict($provider->id, $slotStart, $slotEnd)) {
                        $slots->push([
                            'starts_at' => $slotStart->copy(),
                            'ends_at' => $slotEnd->copy(),
                        ]);

                        if ($slots->count() >= $limit) {
                            break 3;
                        }
                    }

                    $slotStart->addMinutes($duration);
                }
            }

            $cursor->addDay();
        }

        return $slots;
    }

    public function hasConflict(
        ?int $providerId,
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        ?int $exceptAppointmentId = null
    ): bool {
        if ($providerId === null) {
            return false;
        }

        $query = Appointment::query()
            ->where('provider_id', $providerId)
            ->whereNotIn('status', [AppointmentStatus::Cancelled, AppointmentStatus::NoShow])
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt);

        if ($exceptAppointmentId !== null) {
            $query->where('id', '!=', $exceptAppointmentId);
        }

        return $query->exists();
    }
}