<?php

return [
    'brand' => 'AppointRemind',
    'accent' => '#D97706',
    'demo' => [
        'email' => env('DEMO_EMAIL', 'demo@appointremind.test'),
        'password' => env('DEMO_PASSWORD', 'password'),
    ],
    'default_reminder_hours' => (int) env('APPOINT_DEFAULT_REMINDER_HOURS', 24),
    'confirmation_window_hours' => (int) env('APPOINT_CONFIRMATION_WINDOW_HOURS', 48),
    'no_show_risk_threshold' => (float) env('APPOINT_NO_SHOW_RISK_THRESHOLD', 0.55),
];
