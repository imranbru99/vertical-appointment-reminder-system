@extends('layouts.marketing')
@section('title', 'Smart Scheduling — AppointRemind')
@section('content')
<h1 class="section-title">Smart scheduling board</h1>
<p>Provider availability, conflict-aware slots, and live appointments.</p>
<table>
<thead><tr><th>Ref</th><th>Client</th><th>Provider</th><th>Service</th><th>When</th><th>Status</th></tr></thead>
<tbody>
@foreach($appointments as $appt)
<tr>
<td>{{ $appt->reference }}</td>
<td>{{ $appt->client?->name }}</td>
<td>{{ $appt->provider?->name }}</td>
<td>{{ $appt->service?->name }}</td>
<td>{{ $appt->starts_at?->format('M j, g:i A') }}</td>
<td>{{ $appt->status?->label() ?? $appt->status }}</td>
</tr>
@endforeach
</tbody>
</table>
@endsection
