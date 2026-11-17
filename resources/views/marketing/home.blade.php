@extends('layouts.marketing')
@section('title', 'AppointRemind — Vertical Appointment & Reminder System')
@section('content')
<section class="hero">
    <span class="badge">Clinics · Services · No-show defense</span>
    <h1>AppointRemind</h1>
    <p>Smart scheduling, SMS/email reminders, waitlist automation, and no-show reduction — toggle only the modules your clinic or service desk needs.</p>
    <div class="cta">
        <a class="btn" href="{{ route('demo') }}">Try the demo</a>
        <a class="btn ghost" href="{{ route('modules') }}">Browse modules</a>
        <a class="btn ghost" href="/portal">Client portal</a>
    </div>
</section>
<section class="grid">
    <div class="card"><span class="badge">Live</span><h3>{{ $upcoming }} upcoming</h3><p>Booked appointments on the board.</p></div>
    <div class="card"><span class="badge">Engagement</span><h3>{{ $remindersSent }} reminders sent</h3><p>SMS and email delivery tracked.</p></div>
    <div class="card"><span class="badge">Fill rate</span><h3>{{ $waitlist }} on waitlist</h3><p>Ready to absorb cancellations.</p></div>
    <div class="card"><span class="badge">ROI</span><h3>~{{ $weeklyHours }} hrs/week</h3><p>{{ $enabledCount }} modules enabled.</p></div>
</section>
<h2 class="section-title">Featured services</h2>
<section class="grid">
@foreach($services as $service)
    <div class="card"><h3>{{ $service->name }}</h3><p>{{ $service->duration_minutes }} min · {{ $service->category }} · {{ number_format($service->price, 2) }} {{ $service->currency }}</p></div>
@endforeach
</section>
<h2 class="section-title">Providers on duty</h2>
<section class="grid">
@foreach($providers as $provider)
    <div class="card"><h3>{{ $provider->name }}</h3><p>{{ $provider->specialty }} · capacity {{ $provider->daily_capacity }}/day</p></div>
@endforeach
</section>
@endsection
