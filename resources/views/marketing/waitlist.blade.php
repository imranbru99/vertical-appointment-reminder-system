@extends('layouts.marketing')
@section('title', 'Waitlist — AppointRemind')
@section('content')
<h1 class="section-title">Waitlist automation</h1>
<table>
<thead><tr><th>Client</th><th>Service</th><th>Provider</th><th>Priority</th><th>Status</th><th>Preferred</th></tr></thead>
<tbody>
@foreach($entries as $entry)
<tr>
<td>{{ $entry->client?->name }}</td>
<td>{{ $entry->service?->name ?? 'Any' }}</td>
<td>{{ $entry->provider?->name ?? 'Any' }}</td>
<td>{{ $entry->priority }}</td>
<td>{{ $entry->status?->label() ?? $entry->status }}</td>
<td>{{ $entry->preferred_date?->format('M j') }} {{ $entry->preferred_time_window }}</td>
</tr>
@endforeach
</tbody>
</table>
@endsection
