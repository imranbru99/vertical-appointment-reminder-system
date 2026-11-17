<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\ReminderChannel;
use App\Enums\UserRole;
use App\Enums\WaitlistStatus;
use App\Models\Appointment;
use App\Models\AvailabilitySlot;
use App\Models\Client;
use App\Models\Provider;
use App\Models\ReminderTemplate;
use App\Models\Service;
use App\Models\User;
use App\Models\WaitlistEntry;
use App\Services\ModuleManager;
use App\Services\NoShowRiskService;
use App\Services\ReminderDispatcher;
use App\Services\WaitlistService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        app(ModuleManager::class)->syncFromConfig();

        $password = Hash::make((string) config('appointremind.demo.password', 'password'));

        User::query()->updateOrCreate(
            ['email' => 'admin@appointremind.test'],
            [
                'name' => 'Platform Super Admin',
                'password' => $password,
                'role' => UserRole::SuperAdmin,
                'job_title' => 'Platform Owner',
                'organization_name' => 'AppointRemind',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'ops-admin@appointremind.test'],
            [
                'name' => 'Clinic Ops Admin',
                'password' => $password,
                'role' => UserRole::Admin,
                'job_title' => 'Operations Director',
                'organization_name' => 'AppointRemind',
                'is_active' => true,
            ]
        );

        $manager = User::query()->updateOrCreate(
            ['email' => 'manager@appointremind.test'],
            [
                'name' => 'Front Desk Manager',
                'password' => $password,
                'role' => UserRole::Manager,
                'job_title' => 'Scheduling Manager',
                'organization_name' => 'AppointRemind',
                'is_active' => true,
            ]
        );

        $staff = User::query()->updateOrCreate(
            ['email' => 'staff@appointremind.test'],
            [
                'name' => 'Dr. Maya Rahman',
                'password' => $password,
                'role' => UserRole::Staff,
                'job_title' => 'Lead Provider',
                'organization_name' => 'AppointRemind Clinic',
                'phone' => '+8801700001001',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'demo@appointremind.test'],
            [
                'name' => 'Demo Scheduler',
                'password' => $password,
                'role' => UserRole::Demo,
                'job_title' => 'Demo User',
                'organization_name' => 'AppointRemind',
                'is_active' => true,
            ]
        );

        $portalUser = User::query()->updateOrCreate(
            ['email' => 'client@appointremind.test'],
            [
                'name' => 'Portal Client',
                'password' => $password,
                'role' => UserRole::Client,
                'job_title' => 'Patient',
                'organization_name' => 'Self',
                'phone' => '+8801700002002',
                'is_active' => true,
            ]
        );

        $services = [
            ['General Consult', 'general-consult', 'clinic', 30, 75],
            ['Dental Cleaning', 'dental-cleaning', 'dental', 45, 120],
            ['Physio Session', 'physio-session', 'therapy', 60, 95],
            ['Follow-up Visit', 'follow-up-visit', 'clinic', 20, 45],
        ];

        $serviceModels = [];
        foreach ($services as [$name, $slug, $category, $mins, $price]) {
            $serviceModels[$slug] = Service::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'category' => $category,
                    'duration_minutes' => $mins,
                    'buffer_minutes' => 5,
                    'price' => $price,
                    'currency' => 'USD',
                    'is_active' => true,
                    'description' => "{$name} packaged for vertical appointment ops.",
                ]
            );
        }

        $providerMaya = Provider::query()->updateOrCreate(
            ['slug' => 'maya-rahman'],
            [
                'user_id' => $staff->id,
                'name' => 'Dr. Maya Rahman',
                'specialty' => 'Family Medicine',
                'email' => 'staff@appointremind.test',
                'phone' => '+8801700001001',
                'daily_capacity' => 14,
                'is_active' => true,
                'bio' => 'Lead clinic provider with high confirmation rates.',
            ]
        );

        $providerLeo = Provider::query()->updateOrCreate(
            ['slug' => 'leo-chen'],
            [
                'user_id' => null,
                'name' => 'Leo Chen, DPT',
                'specialty' => 'Physiotherapy',
                'email' => 'leo@appointremind.test',
                'phone' => '+8801700001002',
                'daily_capacity' => 10,
                'is_active' => true,
                'bio' => 'Therapy slots with buffer-aware scheduling.',
            ]
        );

        foreach ([$providerMaya, $providerLeo] as $provider) {
            foreach ([1, 2, 3, 4, 5] as $weekday) {
                AvailabilitySlot::query()->updateOrCreate(
                    [
                        'provider_id' => $provider->id,
                        'weekday' => $weekday,
                        'starts_at' => '09:00:00',
                    ],
                    [
                        'ends_at' => '17:00:00',
                        'is_active' => true,
                    ]
                );
            }
        }

        $clients = [
            [$portalUser->id, 'Amina Hasan', 'client@appointremind.test', '+8801700002002', 0],
            [null, 'Jonah Blake', 'jonah@patient.test', '+15550100101', 1],
            [null, 'Priya Sen', 'priya@patient.test', '+15550100102', 2],
            [null, 'Omar Faruk', 'omar@patient.test', '+15550100103', 0],
        ];

        $clientModels = [];
        foreach ($clients as [$userId, $name, $email, $phone, $noShows]) {
            $clientModels[] = Client::query()->updateOrCreate(
                ['email' => $email],
                [
                    'user_id' => $userId,
                    'name' => $name,
                    'phone' => $phone,
                    'no_show_count' => $noShows,
                    'risk_score' => round(min(0.9, 0.1 + $noShows * 0.15), 2),
                    'preferred_channel' => $noShows > 0 ? 'both' : 'email',
                    'notes' => 'Demo client profile',
                ]
            );
        }

        $templates = [
            ['24h Email Confirm', 'email-24h', ReminderChannel::Email, 'Confirm your visit tomorrow', 'Hi {{client}}, reminder for {{service}} with {{provider}} on {{starts_at}} at {{location}}. Ref {{reference}}.', 24],
            ['2h SMS Nudge', 'sms-2h', ReminderChannel::Sms, null, 'AppointRemind: {{service}} today at {{starts_at}}. Reply YES to confirm. Ref {{reference}}.', 2],
            ['Both channels', 'both-12h', ReminderChannel::Both, 'Upcoming appointment', 'Hello {{client}}, your {{service}} is scheduled for {{starts_at}}. Location: {{location}}.', 12],
        ];

        $templateModels = [];
        foreach ($templates as [$name, $slug, $channel, $subject, $body, $hours]) {
            $templateModels[$slug] = ReminderTemplate::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'channel' => $channel,
                    'subject' => $subject,
                    'body' => $body,
                    'hours_before' => $hours,
                    'is_active' => true,
                ]
            );
        }

        $dispatcher = app(ReminderDispatcher::class);
        $risk = app(NoShowRiskService::class);
        $waitlist = app(WaitlistService::class);

        $apptSeeds = [
            ['APT-2401', $clientModels[0], $providerMaya, $serviceModels['general-consult'], now()->addDay()->setTime(10, 0), AppointmentStatus::Confirmed],
            ['APT-2402', $clientModels[1], $providerMaya, $serviceModels['follow-up-visit'], now()->addDays(2)->setTime(11, 30), AppointmentStatus::Scheduled],
            ['APT-2403', $clientModels[2], $providerLeo, $serviceModels['physio-session'], now()->addDays(1)->setTime(14, 0), AppointmentStatus::Scheduled],
            ['APT-2404', $clientModels[3], $providerLeo, $serviceModels['dental-cleaning'], now()->addHours(6), AppointmentStatus::Confirmed],
            ['APT-2390', $clientModels[2], $providerMaya, $serviceModels['general-consult'], now()->subDays(3)->setTime(9, 30), AppointmentStatus::NoShow],
            ['APT-2388', $clientModels[1], $providerLeo, $serviceModels['physio-session'], now()->subDays(1)->setTime(15, 0), AppointmentStatus::Completed],
        ];

        foreach ($apptSeeds as [$ref, $client, $provider, $service, $starts, $status]) {
            $ends = $starts->copy()->addMinutes($service->duration_minutes + $service->buffer_minutes);
            $appointment = Appointment::query()->updateOrCreate(
                ['reference' => $ref],
                [
                    'client_id' => $client->id,
                    'provider_id' => $provider->id,
                    'service_id' => $service->id,
                    'created_by' => $manager->id,
                    'starts_at' => $starts,
                    'ends_at' => $ends,
                    'status' => $status,
                    'location' => 'Main clinic · Room 2',
                    'channel' => 'web',
                    'confirmation_required' => true,
                    'confirmed_at' => $status === AppointmentStatus::Confirmed ? $starts->copy()->subDay() : null,
                    'completed_at' => $status === AppointmentStatus::Completed ? $ends : null,
                    'notes' => 'Seeded demo appointment',
                ]
            );

            $risk->score($appointment);
            if ($status === AppointmentStatus::NoShow) {
                $risk->markNoShow($appointment);
            }

            if (in_array($status, [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed], true)) {
                $reminder = $dispatcher->scheduleFor($appointment, $templateModels['email-24h']);
                if ($starts->isBefore(now()->addDay())) {
                    $dispatcher->send($reminder);
                }
                $dispatcher->scheduleFor($appointment, $templateModels['sms-2h']);
            }
        }

        WaitlistEntry::query()->updateOrCreate(
            [
                'client_id' => $clientModels[3]->id,
                'service_id' => $serviceModels['general-consult']->id,
            ],
            [
                'provider_id' => $providerMaya->id,
                'status' => WaitlistStatus::Waiting,
                'priority' => 80,
                'preferred_date' => now()->addDay()->toDateString(),
                'preferred_time_window' => 'morning',
                'notes' => 'Wants earliest cancellation.',
            ]
        );

        WaitlistEntry::query()->updateOrCreate(
            [
                'client_id' => $clientModels[1]->id,
                'service_id' => $serviceModels['physio-session']->id,
            ],
            [
                'provider_id' => $providerLeo->id,
                'status' => WaitlistStatus::Waiting,
                'priority' => 65,
                'preferred_date' => now()->addDays(2)->toDateString(),
                'preferred_time_window' => 'afternoon',
                'notes' => 'Flexible on provider.',
            ]
        );

        $cancelled = Appointment::query()->updateOrCreate(
            ['reference' => 'APT-CANC-01'],
            [
                'client_id' => $clientModels[0]->id,
                'provider_id' => $providerMaya->id,
                'service_id' => $serviceModels['general-consult']->id,
                'created_by' => $manager->id,
                'starts_at' => now()->addDays(1)->setTime(9, 0),
                'ends_at' => now()->addDays(1)->setTime(9, 35),
                'status' => AppointmentStatus::Cancelled,
                'location' => 'Main clinic · Room 1',
                'channel' => 'phone',
                'confirmation_required' => false,
                'cancelled_at' => now(),
                'notes' => 'Freed for waitlist demo',
            ]
        );

        $waitlist->offerNext($cancelled);
    }
}
