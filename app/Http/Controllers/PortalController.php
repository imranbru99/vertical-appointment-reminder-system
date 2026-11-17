<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\WaitlistEntry;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function index(): View
    {
        return view('portal.index', [
            'appointments' => Appointment::query()
                ->with(['provider', 'service'])
                ->where('starts_at', '>=', now()->subDay())
                ->orderBy('starts_at')
                ->take(10)
                ->get(),
            'waitlist' => WaitlistEntry::query()
                ->with(['service', 'provider'])
                ->whereIn('status', ['waiting', 'offered'])
                ->orderByDesc('priority')
                ->take(5)
                ->get(),
        ]);
    }
}
