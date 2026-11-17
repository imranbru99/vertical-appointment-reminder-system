@extends('layouts.marketing')
@section('title', 'Reminders — AppointRemind')
@section('content')
<h1 class="section-title">SMS & email reminder log</h1>
<table>
<thead><tr><th>Appointment</th><th>Client</th><th>Channel</th><th>Status</th><th>Scheduled</th><th>Sent</th></tr></thead>
<tbody>
@foreach($reminders as $reminder)
<tr>
<td>{{ $reminder->appointment?->reference }}</td>
<td>{{ $reminder->appointment?->client?->name }}</td>
<td>{{ $reminder->channel?->label() ?? $reminder->channel }}</td>
<td>{{ $reminder->status?->label() ?? $reminder->status }}</td>
<td>{{ $reminder->scheduled_for?->format('M j, g:i A') }}</td>
<td>{{ $reminder->sent_at?->format('M j, g:i A') ?? '—' }}</td>
</tr>
@endforeach
</tbody>
</table>
@endsection
