<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;

class NoShowRiskService
{
    public function __construct(private ModuleManager $modules) {}

    public function score(Appointment $appointment): float
    {
        if (! $this->modules->enabled('no_show')) {
            return 0.0;
        }

        $client = $appointment->client;
        $risk = 0.15;

        if ($client) {
            $risk += min(0.5, $client->no_show_count * 0.08);
            $risk += min(0.2, (float) $client->risk_score / 100);
        }

        if ($appointment->confirmation_required && $appointment->confirmed_at === null) {
            $risk += 0.2;
        }

        if ($appointment->starts_at && $appointment->starts_at->diffInHours(now(), false) < 24) {
            $risk += 0.05;
        }

        return round(min(1.0, $risk), 2);
    }

    public function markNoShow(Appointment $appointment): void
    {
        $appointment->update([
            'status' => AppointmentStatus::NoShow,
            'cancelled_at' => now(),
        ]);

        if ($appointment->client) {
            $this->recalcClientRisk($appointment->client);
        }
    }

    public function recalcClientRisk(Client $client): void
    {
        $noShows = $client->appointments()
            ->where('status', AppointmentStatus::NoShow)
            ->count();

        $completed = max(1, $client->appointments()
            ->where('status', AppointmentStatus::Completed)
            ->count());

        $riskScore = round(min(100, ($noShows / $completed) * 100 + $noShows * 5), 2);

        $client->update([
            'no_show_count' => $noShows,
            'risk_score' => $riskScore,
        ]);
    }
}