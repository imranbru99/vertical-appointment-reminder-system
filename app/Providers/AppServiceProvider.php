<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Policies\AppointmentPolicy;
use App\Services\ModuleManager;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleManager::class);
    }

    public function boot(): void
    {
        View::share('accent', config('appointremind.accent', '#D97706'));
        View::share('brand', config('appointremind.brand', 'AppointRemind'));

        Gate::policy(Appointment::class, AppointmentPolicy::class);
    }
}