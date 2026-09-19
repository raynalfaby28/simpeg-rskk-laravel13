@extends('layouts.app')
@section('title', 'Data Pegawai')
@section('crumb', 'Kepegawaian')
@section('nav-employees', 'active')

@section('content')
@php $canManage = auth()->user()->role !== 'user'; @endphp
<div class="page-head">
  <div>
    <h1 class="page-title">Data Pegawai</h1>
    <p class="page-desc">
      {{ $canManage ? 'Kelola seluruh data pegawai RSKK' : 'Lihat data pegawai RSKK' }} &mdash; identitas, jabatan, unit kerja, dan kelengkapan berkas.
    </p>
  </div>
  @if($canManage)
  <div class="flex items-center gap-2">
    <a href="{{ route('employees.export', request()->only(['q','status','unit_id','position_id','rank_id'])) }}" class="btn btn-outline">
      <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
      Ekspor CSV
    </a>
    <a href="{{ route('employees.create') }}" class="btn btn-primary">
      <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Tambah Pegawai
    </a>
  </div>
  @endif
</div>

@php
  $empTotals = [
    ['Total Pegawai', $totals['semua'] ?? 0, 'var(--blue-600)', 'var(--blue-50)',
      '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.9M9 20H4v-2a4 4 0 013-3.9m6-1.6a4 4 0 100-8m6 2a4 4 0 11-8 0 4 4 0 018 0z"/>'],
    ['Pegawai Aktif', $totals['aktif'] ?? 0, 'var(--green-600)', 'var(--green-50)',
      '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    ['Pegawai Nonaktif', ($totals['semua'] ?? 0) - ($totals['aktif'] ?? 0), 'var(--red-600)', 'var(--red-50)',
      '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    ['Pegawai Baru (30 hari)', $totals['baru'] ?? 0, 'var(--teal-600)', 'var(--teal-50)',
      '<path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zm-4 7a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6z"/>'],
  ];
@endphp
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-4 stagger">
  @foreach ($empTotals as [$lbl, $val, $color, $bg, $icon])
    <div class="stat-card flex items-center gap-3.5">
      <div class="stat-ic" style="background:{{ $bg }}; color:{{ $color }}">
        <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $icon !!}</svg>
      </div>
      <div class="min-w-0">
        <div class="stat-val text-[21px]" style="color:{{ $color }}">{{ $val }}</div>
        <div class="text-[11px] font-medium truncate" style="color:var(--ink-500)">{{ $lbl }}</div>
      </div>
    </div>
  @endforeach
</div>

@php
  $statusChips = [
    'semua' => ['Semua', $totals['semua'], 'var(--blue-600)'],
    'aktif' => ['Aktif', $totals['aktif'], 'var(--green-600)'],
    'nonaktif' => ['Nonaktif', max($totals['semua'] - $totals['aktif'], 0), 'var(--red-600)'],
    'baru' => ['Baru 30 Hari', $totals['baru'], 'var(--teal-600)'],
  ];
  $curStatus = request('status', 'semua');
@endphp
<div class="flex flex-wrap items-center gap-2 mb-4">
  @foreach ($statusChips as $key => [$lbl, $val, $color])
    @php $isActive = $curStatus === $key; @endphp
    <a href="{{ route('employees.index', array_merge(request()->except(['page']), $key === 'semua' ? ['status' => $key] : ['status' => $key])) }}"
       class="chip {{ $isActive ? 'active' : '' }}"
       style="{{ $isActive ? 'border-color:' . $color : '' }}">
      <span class="dot" style="background:{{ $color }}"></span>
      {{ $lbl }} <span class="opacity-80">{{ $val }}</span>
    </a>
  @endforeach
</div>

@php
  $completenessFields = ['nik','tempat_lahir','tanggal_lahir','jenis_kelamin','agama','hp','email_resmi','status_perkawinan','no_kk','no_npwp'];
  $activeFilters = collect();
  if(request('q'))           $activeFilters->push(['q', 'Search', request('q')]);
  if(request('unit_id'))     $activeFilters->push(['unit_id', 'Unit', $workUnits->firstWhere('id', request('unit_id'))?->name ?? '']);
  if(request('position_id')) $activeFilters->push(['position_id', 'Jabatan', $positions->firstWhere('id', request('position_id'))?->name ?? '']);
  if(request('rank_id'))     $activeFilters->push(['rank_id', 'Golongan', $ranks->firstWhere('id', request('rank_id'))?->golongan ?? '']);
  if(request('status') && request('status') !== 'semua')
    $activeFilters->push(['status', 'Status', request('status')]);
@endphp

<div class="card overflow-hidden">
  {{-- Toolbar --}}
  <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[minmax(200px,1.4fr)_minmax(150px,.8fr)_minmax(150px,.8fr)_minmax(150px,.8fr)_auto] gap-2.5 p-4" style="border-bottom:1px solid var(--line-soft)">
    <div class="relative">
      <svg class="absolute left-3.5 top-1/2 -translate-y-1/2" style="width:16px;height:16px;color:var(--ink-300)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari NIP, nama, atau unit..."
        class="input" style="padding-left:38px">
    </div>
    <select name="unit_id" class="input" onchange="this.form.submit()">
      <option value="">Unit Kerja</option>
      @foreach ($workUnits as $wu)
        <option value="{{ $wu->id }}" @selected(request('unit_id') == $wu->id)>{{ $wu->name }}</option>
      @endforeach
    </select>
    <select name="position_id" class="input" onchange="this.form.submit()">
      <option value="">Jabatan</option>
      @foreach ($positions as $p)
        <option value="{{ $p->id }}" @selected(request('position_id') == $p->id)>{{ $p->name }}</option>
      @endforeach
    </select>
    <select name="rank_id" class="input" onchange="this.form.submit()">
      <option value="">Golongan</option>
      @foreach ($ranks as $r)
        <option value="{{ $r->id }}" @selected(request('rank_id') == $r->id)>{{ $r->golongan }}@if($r->pangkat) · {{ $r->pangkat }}@endif</option>
      @endforeach
    </select>
    <div class="flex gap-2">
      <button class="btn btn-outline flex-1">Filter</button>
      <a href="{{ route('employees.index') }}" class="btn btn-outline" title="Reset filter" aria-label="Reset filter">
        <svg style="width:13px;height:13px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Reset
      </a>
    </div>
  </form>

  @if($activeFilters->isNotEmpty())
  <div class="flex flex-wrap items-center gap-2 px-4 pt-3 pb-1" style="font-size:12px;color:var(--ink-500)">
    <span class="font-semibold mr-1" style="color:var(--ink-700)">Filter Aktif:</span>
    @foreach($activeFilters as [$key, $label, $val])
      <a href="{{ route('employees.index', request()->except([$key, 'page'])) }}"
         class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-medium"
         style="background:var(--blue-50);border:1px solid var(--blue-100);color:var(--blue-700)">
        {{ $label }}: {{ \Illuminate\Support\Str::limit($val, 22) }}
        <svg style="width:12px;height:12px;opacity:.6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </a>
    @endforeach
  </div>
  @endif

  {{-- Table --}}
  <div class="table-wrap">
    <table class="tbl min-w-[920px]">
      <thead>
        <tr>
          <th class="table-th" style="width:46px">No</th>
          <th class="table-th" style="width:54px">Foto</th>
          <th class="table-th">Nama Pegawai</th>
          <th class="table-th">Jabatan</th>
          <th class="table-th">Unit Kerja</th>
          <th class="table-th">Status</th>
          <th class="table-th text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($employees as $employee)
          @php
            $st = $employee->status_pegawai ?? ($employee->employmentStatus?->name ?? null);
            $isActive = $st ? stripos($st, 'aktif') !== false : false;
            $stPill = $isActive
              ? ['bg' => 'var(--green-50)', 'fg' => 'var(--green-600)', 'bd' => 'var(--green-100)', 'dot' => 'var(--green-600)']
              : ['bg' => 'var(--amber-50)', 'fg' => 'var(--amber-600)', 'bd' => 'var(--amber-100)', 'dot' => 'var(--amber-600)'];
          @endphp
          <tr class="row-line">
            <td class="table-td" style="color:var(--ink-500)">{{ $employees->firstItem() + $loop->index }}</td>
            <td class="table-td">
              <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->nama_lengkap ?: 'Draft') }}&background=2563EB&color=fff&size=64"
                   class="w-9 h-9 rounded-full object-cover" style="border:2px solid #fff" alt="">
            </td>
            <td class="table-td min-w-0">
              <a href="{{ route('employees.show', $employee) }}" class="trow-main truncate block" style="color:var(--ink-900);text-decoration:none">{{ $employee->nama_lengkap ?: '(Draft)' }}</a>
              <div class="trow-sub truncate" style="font-family:ui-monospace,monospace;font-size:11.5px">{{ $employee->nip ? 'NIP. ' . $employee->nip : 'NIP belum diisi' }}</div>
              @if($employee->is_draft)
                <span class="inline-flex items-center px-1.5 py-0.5 mt-0.5 rounded text-[9.5px] font-bold tracking-wide" style="background:var(--amber-50);color:var(--amber-700);border:1px solid var(--amber-100)">DRAFT</span>
              @endif
            </td>
            <td class="table-td whitespace-nowrap" style="max-width:220px">
              <span class="truncate block" title="{{ $employee->currentPosition?->name ?? '' }}">{{ $employee->currentPosition?->name ?? '—' }}</span>
            </td>
            <td class="table-td whitespace-nowrap" style="max-width:180px;color:var(--ink-500)">
              <span class="truncate block" title="{{ $employee->workUnit?->name ?? '' }}">{{ $employee->workUnit?->name ?? '—' }}</span>
            </td>
            <td class="table-td whitespace-nowrap">
              @if($st)
                <span class="badge" style="background:{{ $stPill['bg'] }}; color:{{ $stPill['fg'] }}; border-color:{{ $stPill['bd'] }}">
                  <span class="dot" style="background:{{ $stPill['dot'] }}"></span>{{ $st }}
                </span>
              @else
                <span style="color:var(--ink-300)">—</span>
              @endif
            </td>
            <td class="table-td text-right whitespace-nowrap">
              <div class="flex items-center justify-end gap-1.5">
                <a href="{{ route('employees.show', $employee) }}" class="btn btn-primary btn-sm" style="padding:5px 12px;font-size:11.5px">{{ $canManage ? 'Lihat' : 'Lihat Profil' }}</a>
                <a href="{{ route('employees.show', $employee) }}?tab=dokumen" class="icon-btn" style="width:30px;height:30px;border-radius:8px" title="Dokumen">
                  <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2zM15 3v6h6"/></svg>
                </a>
                <a href="{{ route('employees.show', $employee) }}?tab=riwayat" class="icon-btn" style="width:30px;height:30px;border-radius:8px" title="Linimasa">
                  <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </a>
                @if($canManage)
                <a href="{{ route('employees.edit', $employee) }}" class="icon-btn" style="width:30px;height:30px;border-radius:8px" title="Edit data">
                  <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9a4.2 4.2 0 10-5.9 5.9L19 13l6-6-3.6-3.6z"/></svg>
                </a>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="empty-state">
                <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.9M9 20H4v-2a4 4 0 013-3.9m6-1.6a4 4 0 100-8m6 2a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
                <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Belum Ada Pegawai</p>
                <p class="text-[12.5px] mt-1">Tidak ada pegawai yang cocok dengan pencarian ini.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@if($employees->hasPages() || $employees->total() > 0)
<div class="mt-4 flex flex-wrap items-center justify-between gap-3">
  <div class="text-[12px] flex items-center gap-2" style="color:var(--ink-500)">
    Menampilkan
    <strong style="color:var(--ink-700)">{{ $employees->firstItem() ?? 0 }}–{{ $employees->lastItem() ?? 0 }}</strong>
    dari <strong style="color:var(--ink-700)">{{ number_format($employees->total()) }}</strong> pegawai
    <span style="margin:0 4px">·</span>
    <form method="GET" class="inline">
      @foreach (request()->except(['page', 'per_page']) as $k => $v)
        @if(is_array($v))
          @foreach ($v as $vv)<input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">@endforeach
        @else
          <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endif
      @endforeach
      <select name="per_page" class="input !inline-flex !w-auto !py-1 !text-[12px]" onchange="this.form.submit()">
        @foreach ([10, 20, 50, 100] as $n)
          <option value="{{ $n }}" @selected($employees->perPage() === $n)>{{ $n }}</option>
        @endforeach
      </select>
    </form>
    per halaman
  </div>
  <div>{{ $employees->links() }}</div>
</div>
@endif
@endsection