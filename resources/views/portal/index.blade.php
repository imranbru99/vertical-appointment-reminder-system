@extends('layouts.marketing')
@section('title', 'Client Portal — AppointRemind')
@section('content')
<h1 class="section-title">Client portal</h1>
<table>
<thead><tr><th>Ref</th><th>Service</th><th>Provider</th><th>When</th><th>Status</th></tr></thead>
<tbody>
@foreach($appointments as $appt)
<tr>
<td>{{ $appt->reference }}</td>
<td>{{ $appt->service?->name }}</td>
<td>{{ $appt->provider?->name }}</td>
<td>{{ $appt->starts_at?->format('M j, g:i A') }}</td>
<td>{{ $appt->status?->label() ?? $appt->status }}</td>
</tr>
@endforeach
</tbody>
</table>
@endsection
