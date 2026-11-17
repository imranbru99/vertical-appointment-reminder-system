@extends('layouts.marketing')
@section('title', 'Demo — AppointRemind')
@section('content')
<h1 class="section-title">Demo access</h1>
<p>Sign in at <a href="/admin">/admin</a> with the seeded demo account.</p>
<section class="grid">
<div class="card"><h3>Email</h3><p>{{ $email }}</p></div>
<div class="card"><h3>Password</h3><p>{{ $password }}</p></div>
<div class="card"><h3>Roles seeded</h3><p>Super Admin, Admin, Manager, Staff, Client, Demo</p></div>
</section>
@endsection
