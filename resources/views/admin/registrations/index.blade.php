@extends('admin.layout')
@section('title', 'Registrations')
@php
    $tz = config('app.display_timezone');
    $sortLink = function (string $col, string $label) use ($sort, $dir) {
        $next = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
        $arrow = $sort === $col ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
        $url = request()->fullUrlWithQuery(['sort' => $col, 'dir' => $next, 'page' => null]);
        return '<a href="'.e($url).'">'.e($label).$arrow.'</a>';
    };
    $max = max(1, $chart->max('count'));
@endphp
@section('content')
<div class="head">
  <h1>Registrations</h1>
  <div class="actions">
    <a class="btn btn-ghost" href="{{ route('admin.registrations.export', request()->only('q', 'status')) }}">Export CSV</a>
    <a class="btn" href="{{ route('admin.registrations.create') }}">+ Add registration</a>
  </div>
</div>

<section class="stats">
  <div class="card stat"><small>Total registered</small><b>{{ number_format($stats['total']) }}</b></div>
  <div class="card stat"><small>Registered today</small><b>{{ number_format($stats['today']) }}</b></div>
  <div class="card stat"><small>Checked in</small><b>{{ number_format($stats['checked_in']) }}</b></div>
  <div class="card stat"><small>Attendance rate</small><b>{{ $stats['rate'] }}%</b><div class="bar"><span style="width:{{ $stats['rate'] }}%"></span></div></div>
</section>

<section class="card chart" aria-label="Registrations per day, last 14 days">
  <h2>Registrations per day · last 14 days</h2>
  <div class="bars">
    @foreach ($chart as $day)
      <div title="{{ $day['label'] }}: {{ $day['count'] }}">
        @if ($day['count'])<b>{{ $day['count'] }}</b>@endif
        <span style="height:{{ round($day['count'] / $max * 80) }}%"></span>
        <em>{{ $day['label'] }}</em>
      </div>
    @endforeach
  </div>
</section>

<section class="card">
  <form class="filters" method="GET" action="{{ route('admin.registrations.index') }}">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name or notes…" aria-label="Search">
    <select name="status" aria-label="Status" onchange="this.form.submit()">
      <option value="">All statuses</option>
      <option value="checked_in" @selected(request('status') === 'checked_in')>Checked in</option>
      <option value="pending" @selected(request('status') === 'pending')>Not checked in</option>
    </select>
    <input type="hidden" name="sort" value="{{ $sort }}"><input type="hidden" name="dir" value="{{ $dir }}">
    <button class="btn btn-ghost">Search</button>
    @if (request()->hasAny(['q', 'status']) && (request('q') || request('status')))
      <a class="btn btn-ghost" href="{{ route('admin.registrations.index') }}">Clear</a>
    @endif
  </form>

  <form id="bulkForm" method="POST" action="{{ route('admin.registrations.bulk-destroy') }}"
        onsubmit="return confirm('Delete the selected registrations? This can’t be undone.')">
    @csrf @method('DELETE')
  </form>

  @if ($registrations->isEmpty())
    <div class="empty">
      @if (request('q') || request('status')) No registrations match these filters.
      @else No registrations yet. They’ll appear here as soon as guests submit the invitation form. @endif
    </div>
  @else
  <div class="table-wrap">
    <table>
      <thead><tr>
        <th style="width:1%"><input type="checkbox" id="checkAll" aria-label="Select all on this page"></th>
        <th>#</th>
        <th>{!! $sortLink('full_name', 'Name') !!}</th>
        <th>{!! $sortLink('created_at', 'Registered') !!}</th>
        <th>{!! $sortLink('checked_in_at', 'Status') !!}</th>
        <th class="hide-sm">Source</th>
        <th></th>
      </tr></thead>
      <tbody>
      @foreach ($registrations as $r)
        <tr>
          <td><input type="checkbox" name="ids[]" value="{{ $r->id }}" form="bulkForm" class="rowCheck" aria-label="Select {{ $r->full_name }}"></td>
          <td class="num">{{ $registrations->firstItem() + $loop->index }}</td>
          <td class="name">{{ $r->full_name }}@if ($r->notes)<span class="sub" title="{{ $r->notes }}">{{ $r->notes }}</span>@endif</td>
          <td title="{{ $r->created_at->timezone($tz)->format('Y-m-d H:i:s') }}">{{ $r->created_at->timezone($tz)->format('M j, g:i A') }}</td>
          <td>
            @if ($r->checked_in_at)
              <span class="pill pill-ok" title="{{ $r->checked_in_at->timezone($tz)->format('M j, g:i A') }}">Checked in · {{ $r->checked_in_at->timezone($tz)->format('g:i A') }}</span>
            @else
              <span class="pill pill-wait">Not checked in</span>
            @endif
          </td>
          <td class="hide-sm"><span class="pill pill-src">{{ $r->source }}</span></td>
          <td class="row-actions">
            <form method="POST" action="{{ route('admin.registrations.check-in', $r) }}">@csrf @method('PATCH')
              <button class="btn btn-sm {{ $r->checked_in_at ? 'btn-ghost' : '' }}">{{ $r->checked_in_at ? 'Undo check-in' : 'Check in' }}</button>
            </form>
            <a class="btn btn-ghost btn-sm" href="{{ route('admin.registrations.edit', $r) }}">Edit</a>
            <form method="POST" action="{{ route('admin.registrations.destroy', $r) }}" onsubmit="return confirm('Delete {{ addslashes($r->full_name) }}?')">@csrf @method('DELETE')
              <button class="btn btn-ghost btn-sm" style="color:var(--danger)">Delete</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif

  <div class="foot">
    <button class="btn btn-danger btn-sm" form="bulkForm" id="bulkBtn" disabled>Delete selected</button>
    <span class="muted">
      @if ($registrations->total())
        Showing {{ $registrations->firstItem() }}–{{ $registrations->lastItem() }} of {{ $registrations->total() }}
      @endif
    </span>
    @if ($registrations->hasPages())
      <nav class="pager" aria-label="Pagination">
        @if ($registrations->onFirstPage())<span class="off">‹ Prev</span>@else<a href="{{ $registrations->previousPageUrl() }}">‹ Prev</a>@endif
        @foreach ($registrations->getUrlRange(max(1, $registrations->currentPage() - 2), min($registrations->lastPage(), $registrations->currentPage() + 2)) as $page => $url)
          @if ($page == $registrations->currentPage())<span class="on">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
        @endforeach
        @if ($registrations->hasMorePages())<a href="{{ $registrations->nextPageUrl() }}">Next ›</a>@else<span class="off">Next ›</span>@endif
      </nav>
    @endif
  </div>
</section>

<script>
(() => {
  const all = document.getElementById('checkAll');
  const rows = [...document.querySelectorAll('.rowCheck')];
  const btn = document.getElementById('bulkBtn');
  const sync = () => {
    const n = rows.filter(c => c.checked).length;
    btn.disabled = n === 0;
    btn.textContent = n ? `Delete selected (${n})` : 'Delete selected';
    if (all) { all.checked = n && n === rows.length; all.indeterminate = n > 0 && n < rows.length; }
  };
  all?.addEventListener('change', () => { rows.forEach(c => c.checked = all.checked); sync(); });
  rows.forEach(c => c.addEventListener('change', sync));
})();
</script>
@endsection
