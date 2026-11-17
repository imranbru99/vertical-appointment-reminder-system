<?php

use App\Http\Controllers\MarketingController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::controller(MarketingController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/features', 'features')->name('features');
    Route::get('/modules', 'modules')->name('modules');
    Route::get('/scheduling', 'scheduling')->name('scheduling');
    Route::get('/reminders', 'reminders')->name('reminders');
    Route::get('/waitlist', 'waitlist')->name('waitlist');
    Route::get('/pricing', 'pricing')->name('pricing');
    Route::get('/demo', 'demo')->name('demo');
});

Route::get('/portal', [PortalController::class, 'index'])->name('portal');
