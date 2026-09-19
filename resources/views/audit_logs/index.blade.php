@extends('layouts.app')
@section('title', 'Audit Log')
@section('nav-audit', 'active')
@section('crumb', 'Sistem')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Audit Log</h1>
    <p class="page-desc">Jejak seluruh aktivitas penting: siapa, kapan, dan apa yang terjadi.</p>
  </div>
</div>

<form method="GET" class="flex gap-2 mb-4 flex-wrap items-center">
  <select name="module" class="input !w-auto !py-2">
    <option value="">Semua Modul</option>
    @foreach ($modules as $m)
      <option value="{{ $m }}" @selected(request('module') === $m)>{{ $m }}</option>
    @endforeach
  </select>
  <select name="action" class="input !w-auto !py-2">
    <option value="">Semua Aksi</option>
    @foreach ($actions as $a)
      <option value="{{ $a }}" @selected(request('action') === $a)>{{ $a }}</option>
    @endforeach
  </select>
  <button class="btn btn-outline">Filter</button>
  @if(request('module') || request('action'))
    <a href="{{ route('audit.index') }}" class="btn btn-outline">Reset</a>
  @endif
</form>

@if($logs->isEmpty())
  <div class="card p-10 text-center text-sm" style="color:var(--ink-500)">Belum ada aktivitas tercatat.</div>
@else
  <div class="card overflow-hidden">
    <div class="table-wrap">
      <table class="w-full min-w-[900px]">
        <thead>
          <tr>
            <th class="table-th">Waktu</th>
            <th class="table-th">Pengguna</th>
            <th class="table-th">Modul</th>
            <th class="table-th">Aksi</th>
            <th class="table-th">Aktivitas</th>
            <th class="table-th">IP</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($logs as $log)
            <tr class="row-line">
              <td class="table-td whitespace-nowrap" style="color:var(--ink-500)">{{ $log->created_at->format('d M Y H:i') }}</td>
              <td class="table-td">{{ $log->user_name ?? 'Sistem' }}</td>
              <td class="table-td">
                <span class="badge" style="background:var(--teal-50); color:var(--teal-700); border-color:var(--teal-100)">{{ $log->module }}</span>
              </td>
              <td class="table-td">
                @php
                  $actionPill = match ($log->action) {
                    'create' => ['var(--green-50)', 'var(--green-600)', 'var(--green-100)'],
                    'update', 'edit' => ['var(--amber-50)', 'var(--amber-600)', 'var(--amber-100)'],
                    'delete', 'destroy' => ['var(--red-50)', 'var(--red-600)', 'var(--red-100)'],
                    'approve' => ['var(--blue-50)', 'var(--blue-600)', 'var(--blue-100)'],
                    'reject' => ['var(--red-50)', 'var(--red-600)', 'var(--red-100)'],
                    'login', 'logout' => ['#F3F2ED', 'var(--ink-500)', 'var(--line)'],
                    default => ['var(--teal-50)', 'var(--teal-700)', 'var(--teal-100)'],
                  };
                @endphp
                <span class="badge capitalize" style="background:{{ $actionPill[0] }}; color:{{ $actionPill[1] }}; border-color:{{ $actionPill[2] }}">{{ $log->action }}</span>
              </td>
              <td class="table-td" style="color:var(--ink-700)">{{ $log->description }}</td>
              <td class="table-td" style="color:var(--ink-300)">{{ $log->ip_address }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="mt-4">{{ $logs->links() }}</div>
@endif
@endsection