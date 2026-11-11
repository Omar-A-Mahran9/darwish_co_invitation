@extends('admin.layout')
@php($editing = $registration->exists)
@section('title', $editing ? 'Edit registration' : 'Add registration')
@section('content')
<div class="head">
  <h1>{{ $editing ? 'Edit registration' : 'Add registration' }}</h1>
  <div class="actions"><a class="btn btn-ghost" href="{{ route('admin.registrations.index') }}">← Back</a></div>
</div>
<div class="card form-card">
  <form method="POST" action="{{ $editing ? route('admin.registrations.update', $registration) : route('admin.registrations.store') }}">
    @csrf
    @if ($editing) @method('PUT') @endif
    <div class="field">
      <label for="full_name">Full name</label>
      <input type="text" id="full_name" name="full_name" maxlength="120" required autofocus value="{{ old('full_name', $registration->full_name) }}">
    </div>
    <div class="field">
      <label for="notes">Notes <span style="color:var(--muted);font-weight:400">(internal, optional)</span></label>
      <textarea id="notes" name="notes" rows="4" maxlength="2000">{{ old('notes', $registration->notes) }}</textarea>
    </div>
    <div class="field">
      <label class="check"><input type="checkbox" name="checked_in" value="1" @checked(old('checked_in', (bool) $registration->checked_in_at))> Checked in at the event</label>
    </div>
    @if ($editing)
      <p style="color:var(--muted);font-size:12px;margin:0 0 16px">
        Registered {{ $registration->created_at->timezone(config('app.display_timezone'))->format('M j, Y · g:i A') }} via {{ $registration->source }}
        @if ($registration->ip_address) · IP {{ $registration->ip_address }} @endif
      </p>
    @endif
    <button class="btn">{{ $editing ? 'Save changes' : 'Add registration' }}</button>
  </form>
</div>
@endsection
