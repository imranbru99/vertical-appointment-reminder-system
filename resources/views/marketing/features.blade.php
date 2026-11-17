@extends('layouts.marketing')
@section('title', 'Features — AppointRemind')
@section('content')
<h1 class="section-title">Everything for appointment ops</h1>
<p>Enable or disable modules per business — clinics, salons, trades, and specialty services share one platform.</p>
@foreach($modules as $category => $items)
<h2 class="section-title" style="font-size:1.4rem;text-transform:capitalize;">{{ $category }}</h2>
<section class="grid">
@foreach($items as $module)
    <div class="card"><span class="badge">{{ $module['enabled'] ? 'On' : 'Off' }}</span><h3>{{ $module['name'] }}</h3><p>{{ $module['description'] }}</p></div>
@endforeach
</section>
@endforeach
@endsection
