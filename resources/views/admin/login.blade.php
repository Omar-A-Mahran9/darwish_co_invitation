@extends('admin.layout')
@section('title', 'Log in')
@section('content')
<div class="card form-card" style="margin:60px auto 0;max-width:400px">
  <h1 style="margin-bottom:4px">Admin login</h1>
  <p style="color:var(--muted);margin:0 0 20px">Digilians Training Program — registrations dashboard</p>
  <form method="POST" action="{{ route('admin.login') }}">
    @csrf
    <div class="field"><label for="email">Email</label><input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"></div>
    <div class="field"><label for="password">Password</label><input type="password" id="password" name="password" required autocomplete="current-password"></div>
    <div class="field"><label class="check"><input type="checkbox" name="remember" value="1"> Keep me logged in</label></div>
    <button class="btn" style="width:100%;justify-content:center;padding:10px">Log in</button>
  </form>
</div>
@endsection
