@extends('layouts.marketing')
@section('title', 'Pricing ROI — AppointRemind')
@section('content')
<h1 class="section-title">ROI-first pricing narrative</h1>
<p>With {{ $enabledCount }} of {{ $moduleCount }} modules enabled, demo ops reclaim ~{{ $weeklyHours }} hours/week — about <strong>${{ number_format($weeklyValue) }}</strong> at a blended $65/hr front-desk rate.</p>
<section class="grid">
<div class="card"><h3>Starter</h3><p>Core scheduling + reminders for a single location.</p></div>
<div class="card"><h3>Growth</h3><p>Waitlist, no-show risk, multi-provider capacity.</p></div>
<div class="card"><h3>Enterprise</h3><p>AI slot assist, PDF packs, attendance bridge, catalog scrape.</p></div>
</section>
<a class="btn" href="{{ route('demo') }}">Open demo credentials</a>
@endsection
