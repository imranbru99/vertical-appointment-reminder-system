@extends('layouts.marketing')
@section('title', 'Modules — AppointRemind')
@section('content')
<h1 class="section-title">Toggleable module matrix</h1>
<p>Estimated reclaim: <strong>~{{ $weeklyHours }} hours/week</strong> across enabled modules.</p>
<table>
<thead><tr><th>Module</th><th>Category</th><th>Hrs/wk</th><th>Status</th><th>Core</th></tr></thead>
<tbody>
@foreach($modules as $module)
<tr>
<td><strong>{{ $module['name'] }}</strong><br><small>{{ $module['description'] }}</small></td>
<td>{{ $module['category'] }}</td>
<td>{{ $module['hours_saved_weekly'] }}</td>
<td>{{ $module['enabled'] ? 'Enabled' : 'Disabled' }}</td>
<td>{{ !empty($module['core']) ? 'Yes' : 'No' }}</td>
</tr>
@endforeach
</tbody>
</table>
@endsection
