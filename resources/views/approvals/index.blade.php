@extends('layouts.app')
@section('title', 'Approval Center')
@section('nav-approvals', 'active')
@section('crumb', 'Alur Kerja')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Approval Center</h1>
    <p class="page-desc">Tinjau dan proses pengajuan perubahan data pegawai.</p>
  </div>
  <a href="{{ route('audit.index') }}" class="btn btn-outline btn-sm mt-1">Audit Log</a>
</div>

{{-- Statistik --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6 stagger">
  <a href="{{ route('approvals.index', ['status' => 'pending']) }}" class="stat-card card-hover block" style="{{ $status === 'pending' ? 'border-color:var(--blue-600)' : '' }}">
    <div class="text-[22px] font-bold" style="color:var(--amber-600)">{{ $stats['pending'] }}</div>
    <div class="text-[11.5px] font-medium mt-1" style="color:var(--ink-500)">Menunggu</div>
  </a>
  <a href="{{ route('approvals.index', ['status' => 'reviewed']) }}" class="stat-card card-hover block" style="{{ $status === 'reviewed' ? 'border-color:var(--blue-600)' : '' }}">
    <div class="text-[22px] font-bold" style="color:var(--blue-600)">{{ $stats['reviewed'] }}</div>
    <div class="text-[11.5px] font-medium mt-1" style="color:var(--ink-500)">Sedang Ditinjau</div>
  </a>
  <a href="{{ route('approvals.index', ['status' => 'approved']) }}" class="stat-card card-hover block" style="{{ $status === 'approved' ? 'border-color:var(--blue-600)' : '' }}">
    <div class="text-[22px] font-bold" style="color:var(--green-600)">{{ $stats['approved'] }}</div>
    <div class="text-[11.5px] font-medium mt-1" style="color:var(--ink-500)">Disetujui</div>
  </a>
  <a href="{{ route('approvals.index', ['status' => 'rejected']) }}" class="stat-card card-hover block" style="{{ $status === 'rejected' ? 'border-color:var(--blue-600)' : '' }}">
    <div class="text-[22px] font-bold" style="color:var(--red-600)">{{ $stats['rejected'] }}</div>
    <div class="text-[11.5px] font-medium mt-1" style="color:var(--ink-500)">Ditolak</div>
  </a>
</div>

{{-- Filter --}}
<div class="flex items-center justify-between gap-3 flex-wrap mb-4">
  <div class="flex gap-1.5 flex-wrap">
    @foreach (['pending' => 'Menunggu', 'reviewed' => 'Ditinjau', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'all' => 'Semua'] as $key => $label)
      <a href="{{ route('approvals.index', array_filter(['status' => $key === 'pending' ? null : $key, 'module' => $module, 'q' => $q])) }}"
         class="chip {{ $status === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
  </div>
  <form method="GET" class="flex gap-2 flex-wrap">
    <input type="hidden" name="status" value="{{ $status }}">
    <select name="module" class="input !w-auto !py-1.5 text-[12.5px]">
      <option value="">Semua Modul</option>
      @foreach(['profil' => 'Data Pribadi', 'kepegawaian' => 'Kepegawaian', 'pendidikan' => 'Pendidikan', 'dokumen' => 'Dokumen'] as $v => $l)
        <option value="{{ $v }}" @selected($module === $v)>{{ $l }}</option>
      @endforeach
    </select>
    <input type="text" name="q" value="{{ $q }}" placeholder="Cari pegawai / NIP..." class="input !w-52 !py-1.5 text-[12.5px]">
    <button class="btn-outline px-4 py-1.5 rounded-lg text-[12.5px] font-medium">Filter</button>
  </form>
</div>

<div class="card overflow-hidden">
  <div class="table-wrap">
    <table class="w-full min-w-[760px]">
      <thead>
        <tr>
          <th class="table-th">Pegawai</th>
          <th class="table-th">Modul</th>
          <th class="table-th">Tanggal Ajuan</th>
          <th class="table-th">Status</th>
          <th class="table-th text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($requests as $req)
          <tr class="row-line">
            <td class="table-td">
              <div class="flex items-center gap-2.5">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($req->employee->nama_lengkap) }}&background=2563EB&color=fff&size=64" class="avatar w-8 h-8">
                <div>
                  <div class="font-medium">{{ $req->employee->nama_lengkap }}</div>
                  <div class="trow-sub">NIP {{ $req->employee->nip }}</div>
                </div>
              </div>
            </td>
            <td class="table-td"><span class="badge capitalize" style="background:var(--blue-50); color:var(--blue-600); border-color:var(--blue-100)">{{ ucfirst($req->module_type) }}</span></td>
            <td class="table-td" style="color:var(--ink-500)">{{ $req->created_at->format('d M Y, H:i') }}</td>
            <td class="table-td">
              @php
                $pill = match ($req->status) {
                  'approved' => ['bg' => 'var(--green-50)', 'fg' => 'var(--green-600)', 'bd' => 'var(--green-100)', 'txt' => 'Disetujui'],
                  'rejected' => ['bg' => 'var(--red-50)', 'fg' => 'var(--red-600)', 'bd' => 'var(--red-100)', 'txt' => 'Ditolak'],
                  'reviewed' => ['bg' => 'var(--blue-50)', 'fg' => 'var(--blue-600)', 'bd' => 'var(--blue-100)', 'txt' => 'Ditinjau'],
                  default      => ['bg' => 'var(--amber-50)', 'fg' => 'var(--amber-600)', 'bd' => 'var(--amber-100)', 'txt' => 'Menunggu'],
                };
              @endphp
              <span class="badge" style="background:{{ $pill['bg'] }}; color:{{ $pill['fg'] }}; border-color:{{ $pill['bd'] }}">
                <span class="dot" style="background:{{ $pill['fg'] }}"></span>{{ $pill['txt'] }}
                @if($req->status === 'rejected' && $req->rejection_reason)
                  <span title="{{ $req->rejection_reason }}">· Lihat</span>
                @endif
              </span>
            </td>
            <td class="table-td text-right">
              <a href="{{ route('approvals.show', $req) }}" class="btn btn-outline btn-sm">Review</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="table-td text-center py-14" style="color:var(--ink-500)">
              <div class="empty-state">
                <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Tidak Ada Pengajuan</p>
                <p class="text-[12.5px] mt-1">Tidak ada pengajuan dengan filter ini.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">{{ $requests->links() }}</div>
@endsection