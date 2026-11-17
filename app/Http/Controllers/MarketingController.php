<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Provider;
use App\Models\Reminder;
use App\Models\Service;
use App\Models\WaitlistEntry;
use App\Services\ModuleManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MarketingController extends Controller
{
    public function home(ModuleManager $modules): View
    {
        $ready = Schema::hasTable('appointments');

        return view('marketing.home', [
            'modules' => $modules->all(),
            'enabledCount' => count($modules->enabledKeys()),
            'weeklyHours' => $modules->weeklyHoursSaved(),
            'upcoming' => $ready
                ? Appointment::query()->where('starts_at', '>=', now())->whereNotIn('status', ['cancelled', 'no_show'])->count()
                : 0,
            'remindersSent' => Schema::hasTable('reminders')
                ? Reminder::query()->whereIn('status', ['sent', 'delivered'])->count()
                : 0,
            'waitlist' => Schema::hasTable('waitlist_entries')
                ? WaitlistEntry::query()->where('status', 'waiting')->count()
                : 0,
            'services' => Schema::hasTable('services')
                ? Service::query()->where('is_active', true)->take(4)->get()
                : collect(),
            'providers' => Schema::hasTable('providers')
                ? Provider::query()->where('is_active', true)->take(3)->get()
                : collect(),
        ]);
    }

    public function features(ModuleManager $modules): View
    {
        return view('marketing.features', [
            'modules' => $modules->all()->groupBy(fn (array $m) => $m['category'] ?? 'general'),
        ]);
    }

    public function modules(ModuleManager $modules): View
    {
        return view('marketing.modules', [
            'modules' => $modules->all(),
            'weeklyHours' => $modules->weeklyHoursSaved(),
        ]);
    }

    public function scheduling(): View
    {
        return view('marketing.scheduling', [
            'providers' => Schema::hasTable('providers')
                ? Provider::query()->with('availabilitySlots')->where('is_active', true)->get()
                : collect(),
            'services' => Schema::hasTable('services')
                ? Service::query()->where('is_active', true)->get()
                : collect(),
            'appointments' => Schema::hasTable('appointments')
                ? Appointment::query()->with(['client', 'provider', 'service'])->latest('starts_at')->take(8)->get()
                : collect(),
        ]);
    }

    public function reminders(): View
    {
        return view('marketing.reminders', [
            'reminders' => Schema::hasTable('reminders')
                ? Reminder::query()->with('appointment.client')->latest()->take(12)->get()
                : collect(),
        ]);
    }

    public function waitlist(): View
    {
        return view('marketing.waitlist', [
            'entries' => Schema::hasTable('waitlist_entries')
                ? WaitlistEntry::query()->with(['client', 'service', 'provider'])->orderByDesc('priority')->get()
                : collect(),
        ]);
    }

    public function pricing(ModuleManager $modules): View
    {
        $hours = $modules->weeklyHoursSaved();

        return view('marketing.pricing', [
            'weeklyHours' => $hours,
            'weeklyValue' => $hours * 65,
            'moduleCount' => $modules->all()->count(),
            'enabledCount' => count($modules->enabledKeys()),
        ]);
    }

    public function demo(): View
    {
        return view('marketing.demo', [
            'email' => config('appointremind.demo.email'),
            'password' => config('appointremind.demo.password'),
        ]);
    }
}
