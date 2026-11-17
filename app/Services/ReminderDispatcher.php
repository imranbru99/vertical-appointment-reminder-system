<?php

namespace App\Services;

use App\Enums\ReminderChannel;
use App\Enums\ReminderStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Reminder;
use App\Models\ReminderTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReminderDispatcher
{
    public function __construct(private ModuleManager $modules) {}

    public function scheduleFor(Appointment $appointment, ?ReminderTemplate $template = null): Reminder
    {
        $template ??= ReminderTemplate::query()
            ->where('is_active', true)
            ->orderBy('hours_before')
            ->first();

        $channel = $template?->channel ?? ReminderChannel::Email;
        $hoursBefore = $template?->hours_before ?? (int) config('appointremind.default_reminder_hours', 24);
        $scheduledFor = $appointment->starts_at->copy()->subHours($hoursBefore);

        if ($scheduledFor->isPast()) {
            $scheduledFor = now();
        }

        $client = $appointment->client;
        $recipient = match ($channel) {
            ReminderChannel::Sms => $client?->phone,
            ReminderChannel::Both => $client?->email,
            default => $client?->email,
        };

        $body = $this->renderBody(
            $template?->body ?? 'Reminder: {{reference}} with {{provider_name}} on {{starts_at}}.',
            $appointment,
            $client
        );

        return Reminder::query()->create([
            'appointment_id' => $appointment->id,
            'channel' => $channel,
            'status' => ReminderStatus::Pending,
            'template_key' => $template?->slug,
            'scheduled_for' => $scheduledFor,
            'recipient' => $recipient,
            'body' => $body,
        ]);
    }

    public function send(Reminder $reminder): bool
    {
        if (! $this->modules->enabled('reminders')) {
            return false;
        }

        $appointment = $reminder->appointment()->with(['client', 'provider', 'service'])->first();

        if ($appointment === null) {
            $reminder->update(['status' => ReminderStatus::Failed]);

            return false;
        }

        $body = $reminder->body ?: $this->renderBody(
            'Reminder: {{reference}}',
            $appointment,
            $appointment->client
        );

        try {
            if (in_array($reminder->channel, [ReminderChannel::Email, ReminderChannel::Both], true)
                && filled($reminder->recipient)) {
                Mail::raw($body, function ($message) use ($reminder, $appointment) {
                    $message->to($reminder->recipient)
                        ->subject('Appointment reminder '.$appointment->reference);
                });
            }

            if (in_array($reminder->channel, [ReminderChannel::Sms, ReminderChannel::Both], true)
                && filled($appointment->client?->phone)) {
                Log::info('SMS reminder queued', [
                    'to' => $appointment->client->phone,
                    'reference' => $appointment->reference,
                ]);
            }

            $reminder->update([
                'status' => ReminderStatus::Sent,
                'sent_at' => now(),
                'body' => $body,
            ]);

            return true;
        } catch (\Throwable) {
            $reminder->update(['status' => ReminderStatus::Failed]);

            return false;
        }
    }

    public function renderBody(string $template, Appointment $appointment, ?Client $client = null): string
    {
        $client ??= $appointment->client;

        $replacements = [
            '{{reference}}' => $appointment->reference,
            '{{client_name}}' => $client?->name ?? '',
            '{{provider_name}}' => $appointment->provider?->name ?? 'your provider',
            '{{service_name}}' => $appointment->service?->name ?? '',
            '{{starts_at}}' => $appointment->starts_at?->format('M j, Y g:i A') ?? '',
            '{{ends_at}}' => $appointment->ends_at?->format('M j, Y g:i A') ?? '',
            '{{location}}' => $appointment->location ?? '',
            '{{status}}' => $appointment->status?->label() ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}