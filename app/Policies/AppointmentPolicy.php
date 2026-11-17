<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role !== UserRole::Client;
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->role?->canManageSchedule() ?? false;
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, [UserRole::SuperAdmin, UserRole::Admin, UserRole::Manager], true);
    }
}