@extends('layouts.app')
@section('title', 'Dashboard')
@section('crumb', 'Beranda')
@section('nav-dashboard', 'active')

@section('content')
@php
  $role = $role ?? (auth()->user()->role === 'user' ? 'user' : 'admin');
  $today = now()->translatedFormat('l, d F Y');
@endphp

<div class="page-head">
  <div>
    <h1 class="page-title"><span id="greet">Selamat Datang,</span> {{ strtok(auth()->user()->name, ' ') }}</h1>
    <p class="page-desc">
      <span class="font-semibold" style="color:var(--ink-700)">{{ $today }}</span>
      @if($role === 'user')
        &nbsp;·&nbsp; Pantau progres profil dan pengajuan kamu di SIMPEG RSKK.
      @else
        &nbsp;·&nbsp; Pantau dan kelola informasi kepegawaian rumah sakit.
      @endif
    </p>
  </div>
  @if($role !== 'user')
    @php $hasPending = ($pendingApprovals ?? 0) > 0; @endphp
    <a href="{{ route('approvals.index') }}" class="btn {{ $hasPending ? 'btn-primary' : 'btn-outline' }}">
      <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 01-6 0"/></svg>
      Review Approval
      @if($hasPending)<span class="badge shrink-0" style="background:#fff;color:var(--blue-700);border-color:rgba(255,255,255,.4)">{{ $pendingApprovals }}</span>@endif
    </a>
  @else
    <a href="{{ route('documents.create') }}" class="btn btn-outline">
      <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2zM15 3v6h6"/></svg>
      Unggah Dokumen
    </a>
  @endif
</div>

@if($role === 'user')
  {{-- ============ DASHBOARD USER ============ --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5 stagger">
    <div class="stat-card prem flex items-center gap-4 p-4" style="--c1:#D97706;--c2:#FBBF24">
      <div class="stat-ic">
        <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div class="min-w-0">
        <div class="stat-val text-[22px]" style="color:var(--c1)">{{ $statusCounts['pending'] }}</div>
        <div class="text-[11px] font-medium truncate mt-0.5" style="color:var(--ink-500)">Pengajuan Menunggu</div>
      </div>
    </div>
    <div class="stat-card prem flex items-center gap-4 p-4" style="--c1:#059669;--c2:#34D399">
      <div class="stat-ic">
        <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div class="min-w-0">
        <div class="stat-val text-[22px]" style="color:var(--c1)">{{ $statusCounts['approved'] }}</div>
        <div class="text-[11px] font-medium truncate mt-0.5" style="color:var(--ink-500)">Disetujui</div>
      </div>
    </div>
    <div class="stat-card prem flex items-center gap-4 p-4" style="--c1:#DC2626;--c2:#F87171">
      <div class="stat-ic">
        <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div class="min-w-0">
        <div class="stat-val text-[22px]" style="color:var(--c1)">{{ $statusCounts['rejected'] }}</div>
        <div class="text-[11px] font-medium truncate mt-0.5" style="color:var(--ink-500)">Ditolak</div>
      </div>
    </div>
  </div>

  @php
    $completeness = $employee ? (function () use ($employee) {
        $fields = ['tempat_lahir','tanggal_lahir','jenis_kelamin','agama','nik','hp','email_resmi','no_kk','status_perkawinan','no_npwp'];
        $filled = collect($fields)->filter(fn ($f) => filled($employee->{$f}))->count();
        return round($filled / count($fields) * 100);
    })() : 0;
  @endphp

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    {{-- Ringkasan profil --}}
    <div class="card p-6 lg:col-span-2">
      <div class="flex items-center justify-between mb-4">
        <h3 class="section-title">Ringkasan Profil</h3>
        <a href="{{ route('profile.edit') }}" class="text-[12px] font-semibold" style="color:var(--blue-600)">Lengkapi Data →</a>
      </div>
      @if($employee)
        <div class="flex items-center gap-4 mb-4">
          @if($employee->foto_path)
          <img src="{{ asset('storage/' . $employee->foto_path) }}" class="avatar w-14 h-14" style="--ring:#60A5FA; box-shadow:0 0 0 3px #fff, 0 0 0 5px rgba(37,99,235,.25)" alt="{{ $employee->nama_lengkap }}" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($employee->nama_lengkap) }}&background=2563EB&color=fff&size=96'">
        @else
          <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->nama_lengkap) }}&background=2563EB&color=fff&size=96" class="avatar w-14 h-14" style="--ring:#60A5FA; box-shadow:0 0 0 3px #fff, 0 0 0 5px rgba(37,99,235,.25)" alt="{{ $employee->nama_lengkap }}">
        @endif
          <div>
            <div class="text-[15px] font-semibold">{{ $employee->nama_lengkap_dengan_gelar }}</div>
            <div class="text-[12px]" style="color:var(--ink-500)">NIP {{ $employee->nip }}</div>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
          @foreach ([
            'Jabatan' => $employee->currentPosition?->name,
            'Unit Kerja' => $employee->workUnit?->name,
            'Status' => $employee->employmentStatus?->name,
            'Golongan' => $employee->golonganAkhir ? $employee->golonganAkhir->golongan . ($employee->golonganAkhir->pangkat ? ' — ' . $employee->golonganAkhir->pangkat : '') : null,
            'Jenis ASN' => $employee->jenis_asn,
            'Gaji Pokok' => $employee->gaji_pokok ? (auth()->user()->role === 'super_admin' ? 'Rp ' . number_format($employee->gaji_pokok, 0, ',', '.') : 'Rp •••• (super admin)') : null,
          ] as $label => $value)
            <div class="field-row">
              <span class="text-[12.5px]" style="color:var(--ink-500)">{{ $label }}</span>
              <span class="text-[12.5px] font-medium text-right" style="color:var(--ink-700)">{{ $value ?? '—' }}</span>
            </div>
          @endforeach
        </div>
        <div class="mt-5">
          <div class="flex items-center justify-between mb-1.5">
            <span class="text-[12px] font-semibold" style="color:var(--ink-700)">Kelengkapan Profil</span>
            <span class="text-[12px] font-bold" style="color:var(--blue-600)">{{ $completeness }}%</span>
          </div>
          <div class="progress">
            <div style="width:{{ $completeness }}%; background:linear-gradient(90deg, var(--teal-500), var(--blue-600))"></div>
          </div>
          <p class="text-[11.5px] mt-2" style="color:var(--ink-500)">{{ $completeness >= 100 ? 'Lengkap — semua data terisi.' : 'Lengkapi data untuk mempermudah verifikasi kepegawaian.' }}</p>
        </div>
      @else
        <p class="text-sm" style="color:var(--ink-500)">Data pegawai belum terhubung ke akun ini. Hubungi administrator.</p>
      @endif
    </div>

    {{-- Notifikasi --}}
    <div class="card p-6">
      <h3 class="section-title mb-4">Notifikasi</h3>
      @if($notifications->isEmpty())
        <p class="text-xs" style="color:var(--ink-500)">Belum ada notifikasi.</p>
      @else
        <div class="space-y-3">
          @foreach ($notifications as $n)
            <div class="flex gap-2.5">
              <span class="w-2 h-2 rounded-full mt-1.5 flex-none {{ $n->read_at ? '' : 'dot-live' }}" style="background:{{ $n->read_at ? 'var(--ink-200)' : 'var(--blue-600)' }}"></span>
              <div>
                <div class="text-[12.5px] font-medium" style="color:var(--ink-700)">{{ $n->title }}</div>
                @if($n->body)<div class="text-[12px] mt-0.5" style="color:var(--ink-500)">{{ $n->body }}</div>@endif
                <div class="text-[10.5px] mt-1" style="color:var(--ink-300)">{{ $n->created_at->diffForHumans() }}</div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  {{-- Riwayat pengajuan --}}
  <div class="card overflow-hidden">
    <div class="px-6 pt-5 pb-3 flex items-center justify-between">
      <h3 class="section-title">Riwayat Pengajuan</h3>
    </div>
    @if($changeRequests->isEmpty())
      <div class="empty-state">
        <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
        <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Belum Ada Pengajuan</p>
        <p class="text-[12.5px] mt-1 max-w-sm">Kamu belum pernah mengajukan perubahan data. Gunakan menu Profil Saya untuk mengajukan.</p>
      </div>
    @else
      <div class="table-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th class="table-th">Modul</th>
              <th class="table-th">Diajukan</th>
              <th class="table-th text-right">Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($changeRequests as $cr)
              <tr class="row-line">
                <td class="table-td capitalize font-medium">{{ str_replace('_', ' ', $cr->module_type) }}</td>
                <td class="table-td" style="color:var(--ink-500)">{{ $cr->created_at->format('d M Y, H:i') }}</td>
                <td class="table-td text-right">
                  @php
                    $pill = match ($cr->status) {
                      'approved' => ['bg' => 'var(--green-50)', 'fg' => 'var(--green-600)', 'bd' => 'var(--green-100)', 'txt' => 'Disetujui'],
                      'rejected' => ['bg' => 'var(--red-50)', 'fg' => 'var(--red-600)', 'bd' => 'var(--red-100)', 'txt' => 'Ditolak'],
                      default    => ['bg' => 'var(--amber-50)', 'fg' => 'var(--amber-600)', 'bd' => 'var(--amber-100)', 'txt' => 'Menunggu'],
                    };
                  @endphp
                  <span class="badge" style="background:{{ $pill['bg'] }}; color:{{ $pill['fg'] }}; border-color:{{ $pill['bd'] }}">
                    {{ $pill['txt'] }}
                    @if($cr->status === 'rejected' && $cr->rejection_reason)
                      · {{ \Illuminate\Support\Str::limit($cr->rejection_reason, 30) }}
                    @endif
                  </span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

@else
  {{-- ============ DASHBOARD ADMIN / SUPER ADMIN ============ --}}
  {{-- KPI --}}
  <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6 stagger">
    @php
      $kpis = [
        ['Total Pegawai', $totals['pegawai'], '#2563EB', '#60A5FA',
          '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.9M9 20H4v-2a4 4 0 013-3.9m6-1.6a4 4 0 100-8m6 2a4 4 0 11-8 0 4 4 0 018 0z"/>'],
        ['Pegawai Aktif', $totals['aktif'], '#059669', '#34D399',
          '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['Pegawai Nonaktif', $totals['nonaktif'], '#DC2626', '#F87171',
          '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['Baru 30 Hari', $totals['baru'], '#0D9488', '#2DD4BF',
          '<path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zm-4 7a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6z"/>'],
        ['Menunggu Approval', $pendingApprovals, '#D97706', '#FBBF24',
          '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        ['Dokumen Belum Verif.', $unverifiedDocuments, '#0EA5E9', '#38BDF8',
          '<path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2zM15 3v6h6"/>'],
      ];
    @endphp
    @foreach ($kpis as [$label, $val, $c1, $c2, $icon])
      <div class="stat-card prem flex items-center gap-3.5 p-4" style="--c1:{{ $c1 }};--c2:{{ $c2 }}">
        <div class="stat-ic">
          <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">{!! $icon !!}</svg>
        </div>
        <div class="min-w-0">
          <div class="stat-val text-[21px]" style="color:var(--c1)">{{ $val }}</div>
          <div class="text-[11px] font-medium truncate" style="color:var(--ink-500)">{{ $label }}</div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Perlu Tindakan --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 stagger">
    <a href="{{ route('approvals.index') }}" class="card card-hover min-act p-4 flex items-center gap-3.5" style="--h1:#D97706;--h2:#FBBF24">
      <span class="act-ic">
        <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </span>
      <div class="min-w-0 flex-1">
        <div class="text-[13.5px] font-semibold truncate" style="color:var(--ink-700)">Review Pengajuan Approval</div>
        <div class="text-[11.5px]" style="color:var(--ink-500)">Menunggu keputusan admin</div>
      </div>
      <span class="text-[22px] font-bold shrink-0" style="color:var(--h1)">{{ $pendingApprovals }}</span>
      <svg class="chev w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
    <a href="{{ route('documents.index') }}" class="card card-hover min-act p-4 flex items-center gap-3.5" style="--h1:#DC2626;--h2:#F87171">
      <span class="act-ic">
        <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2zM15 3v6h6"/></svg>
      </span>
      <div class="min-w-0 flex-1">
        <div class="text-[13.5px] font-semibold truncate" style="color:var(--ink-700)">Verifikasi Dokumen</div>
        <div class="text-[11.5px]" style="color:var(--ink-500)">Dokumen belum diverifikasi</div>
      </div>
      <span class="text-[22px] font-bold shrink-0" style="color:var(--h1)">{{ $unverifiedDocuments }}</span>
      <svg class="chev w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
    <a href="{{ route('employees.index') }}" class="card card-hover min-act p-4 flex items-center gap-3.5" style="--h1:#2563EB;--h2:#60A5FA">
      <span class="act-ic">
        <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9a4.2 4.2 0 10-5.9 5.9L19 13l6-6-3.6-3.6z"/></svg>
      </span>
      <div class="min-w-0 flex-1">
        <div class="text-[13.5px] font-semibold truncate" style="color:var(--ink-700)">Profil Belum Lengkap</div>
        <div class="text-[11.5px]" style="color:var(--ink-500)">Perlu dilengkapi pegawai / admin</div>
      </div>
      <span class="text-[22px] font-bold shrink-0" style="color:var(--h1)">{{ $incompleteProfiles }}</span>
      <svg class="chev w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
  </div>

  {{-- Grafik Row 1: Unit + Status (donut) --}}
  @php
    $donutColors = ['#2563EB', '#0EA5E9', '#10B981', '#F59E0B', '#F43F5E', '#8B5CF6', '#64748B'];
    $donutData = $perStatus->toArray();
    $donutTotal = array_sum($donutData) ?: 1;
    $start = -90; $dStops = []; $dIdx = 0; $dLegend = [];
    foreach ($donutData as $k => $v) {
        $deg = intval(round($v / $donutTotal * 360));
        $dLegend[] = [$k, $v, $donutColors[$dIdx % count($donutColors)]];
        if ($deg > 0) {
            $dStops[] = "{$donutColors[$dIdx % count($donutColors)]} {$start}deg " . ($start + $deg) . 'deg';
            $start += $deg;
        }
        $dIdx++;
    }
    $conic = $dStops ? implode(', ', $dStops) : '#E4E7EC 0deg 360deg';
  @endphp
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    {{-- Unit --}}
    <div class="card p-6">
      <h3 class="section-title mb-5">Jumlah Pegawai per Unit</h3>
      @if($perUnit->isEmpty())
        <div class="empty-state"><p class="text-[12.5px]">Belum ada data pegawai.</p></div>
      @else
        @php $maxU = $perUnit->first() ?: 1; @endphp
        <div class="space-y-3.5">
          @foreach ($perUnit as $name => $count)
            <div>
              <div class="flex justify-between text-[12.5px] mb-1.5">
                <span class="truncate" style="color:var(--ink-700)">{{ $name }}</span>
                <span class="font-semibold" style="color:var(--blue-600)">{{ $count }}</span>
              </div>
              <div class="progress">
                <div style="width:{{ $maxU > 0 ? round($count / $maxU * 100) : 0 }}%; background:linear-gradient(90deg, var(--teal-500), var(--blue-600))"></div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- Status donut --}}
    <div class="card p-6">
      <h3 class="section-title mb-5">Status Kepegawaian</h3>
      <div class="flex flex-col items-center gap-5">
        <div class="relative w-[150px] h-[150px] rounded-full donut" style="background:conic-gradient({{ $conic }}); box-shadow:inset 0 0 0 1px var(--line-soft)">
          <div class="absolute inset-[22px] rounded-full flex flex-col items-center justify-center" style="background:#fff">
            <div class="text-[26px] font-bold leading-none" style="color:var(--navy-900)">{{ $donutTotal }}</div>
            <div class="text-[10.5px] font-medium mt-1" style="color:var(--ink-500)">Total Pegawai</div>
          </div>
        </div>
        <div class="w-full space-y-2">
          @foreach ($dLegend as [$name, $count, $color])
            <div class="flex items-center gap-2.5 text-[12px]">
              <span class="w-2.5 h-2.5 rounded-full flex-none" style="background:{{ $color }}"></span>
              <span class="truncate flex-1" style="color:var(--ink-700)">{{ $name }}</span>
              <span class="font-semibold" style="color:var(--ink-900)">{{ $count }}</span>
              <span class="w-10 text-right font-medium" style="color:var(--ink-300)">{{ $donutTotal ? round($count / $donutTotal * 100) : 0 }}%</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Grafik Row 2: Pendidikan + Golongan --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="card p-6">
      <h3 class="section-title mb-5">Pendidikan Terakhir</h3>
      @if($perPendidikan->isEmpty())
        <div class="empty-state"><p class="text-[12.5px]">Belum ada data.</p></div>
      @else
        @php $maxP = $perPendidikan->first() ?: 1; @endphp
        <div class="space-y-3.5">
          @foreach ($perPendidikan as $name => $count)
            <div>
              <div class="flex justify-between text-[12.5px] mb-1.5">
                <span class="truncate" style="color:var(--ink-700)">{{ $name }}</span>
                <span class="font-semibold" style="color:var(--cyan-600)">{{ $count }}</span>
              </div>
              <div class="progress">
                <div style="width:{{ $maxP > 0 ? round($count / $maxP * 100) : 0 }}%; background:linear-gradient(90deg, var(--teal-500), var(--cyan-600))"></div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    <div class="card p-6">
      <h3 class="section-title mb-5">Peringkat Golongan</h3>
      @if($perGolongan->isEmpty())
        <div class="empty-state"><p class="text-[12.5px]">Belum ada data.</p></div>
      @else
        @php $maxG = $perGolongan->first() ?: 1; @endphp
        <div class="space-y-3.5">
          @foreach ($perGolongan as $name => $count)
            <div>
              <div class="flex justify-between text-[12.5px] mb-1.5">
                <span class="font-semibold" style="color:var(--navy-800)">{{ $name }}</span>
                <span style="color:var(--ink-500)">{{ $count }} orang</span>
              </div>
              <div class="progress">
                <div style="width:{{ $maxG > 0 ? round($count / $maxG * 100) : 0 }}%; background:linear-gradient(90deg, var(--blue-600), var(--teal-500))"></div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  {{-- Mutasi & Diklat terbaru --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="card overflow-hidden">
      <div class="px-6 pt-5 pb-3">
        <h3 class="section-title">Mutasi Terbaru</h3>
      </div>
      @if($recentMutations->isEmpty())
        <div class="px-6 pb-6"><div class="empty-state"><p class="text-[12.5px]">Belum ada mutasi tercatat.</p></div></div>
      @else
        <div class="px-6 pb-6 space-y-3">
          @foreach ($recentMutations as $m)
            <div class="flex items-center gap-3.5 px-4 py-3 rounded-xl hrow" style="border:1px solid var(--line-soft)">
              <span class="w-9 h-9 rounded-lg flex items-center justify-center flex-none" style="background:var(--blue-50); color:var(--blue-600)">
                <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l4-4 4 4m-4-4v12M4 17l4 4 4-4"/></svg>
              </span>
              <div class="min-w-0 flex-1">
                <div class="text-[13px] font-semibold truncate" style="color:var(--ink-700)">{{ $m->employee?->nama_lengkap }}</div>
                <div class="text-[11.5px] truncate" style="color:var(--ink-500)">{{ $m->jenis_mutasi }} · {{ $m->unitTujuan?->name ?? '—' }}</div>
              </div>
              <span class="text-[11px] shrink-0" style="color:var(--ink-300)">{{ $m->tanggal_mutasi ? \Illuminate\Support\Carbon::parse($m->tanggal_mutasi)->translatedFormat('d M Y') : '—' }}</span>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    <div class="card overflow-hidden">
      <div class="px-6 pt-5 pb-3">
        <h3 class="section-title">Diklat & Pelatihan Terbaru</h3>
      </div>
      @if($recentTrainings->isEmpty())
        <div class="px-6 pb-6"><div class="empty-state"><p class="text-[12.5px]">Belum ada diklat tercatat.</p></div></div>
      @else
        <div class="px-6 pb-6 space-y-3">
          @foreach ($recentTrainings as $t)
            <div class="flex items-center gap-3.5 px-4 py-3 rounded-xl hrow" style="border:1px solid var(--line-soft)">
              <span class="w-9 h-9 rounded-lg flex items-center justify-center flex-none" style="background:var(--teal-50); color:var(--teal-600)">
                <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              </span>
              <div class="min-w-0 flex-1">
                <div class="text-[13px] font-semibold truncate" style="color:var(--ink-700)">{{ $t->nama_pelatihan }}</div>
                <div class="text-[11.5px] truncate" style="color:var(--ink-500)">{{ $t->employee?->nama_lengkap }}</div>
              </div>
              <span class="text-[11px] shrink-0" style="color:var(--ink-300)">{{ $t->created_at->diffForHumans() }}</span>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  {{-- Sertifikasi + Approval --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <div class="card p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="section-title">Sertifikasi Masa Berlaku</h3>
        <a href="{{ route('reports.index') }}" class="text-[12px] font-semibold" style="color:var(--blue-600)">Buka →</a>
      </div>
      @if($sertifAkts['expired'] === 0 && $sertifAkts['expiring'] === 0)
        <div class="empty-state">
          <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
          <p class="text-[13px] font-semibold" style="color:var(--ink-700)">Tidak Ada Sertifikasi Bermasalah</p>
          <p class="text-[12px] mt-1" style="color:var(--ink-500)">Semua sertifikasi masih aktif.</p>
        </div>
      @else
        <div class="space-y-3">
          @forelse($expiredCerts as $t)
            <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl" style="border:1px solid var(--red-100); background:var(--red-50)">
              <div class="min-w-0">
                <div class="text-[13px] font-semibold truncate" style="color:var(--ink-700)">{{ $t->nama_pelatihan }}</div>
                <div class="text-[11.5px] truncate" style="color:var(--ink-500)">{{ $t->employee?->nama_lengkap }} · habis {{ \Illuminate\Support\Carbon::parse($t->masa_berlaku)->translatedFormat('d M Y') }}</div>
              </div>
              <span class="badge shrink-0" style="background:var(--red-100); color:var(--red-600); border-color:var(--red-200)">Habis</span>
            </div>
          @empty
          @endforelse
          @forelse($expiringCerts as $t)
            <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl" style="border:1px solid var(--amber-100); background:var(--amber-50)">
              <div class="min-w-0">
                <div class="text-[13px] font-semibold truncate" style="color:var(--ink-700)">{{ $t->nama_pelatihan }}</div>
                <div class="text-[11.5px] truncate" style="color:var(--ink-500)">{{ $t->employee?->nama_lengkap }} · habis {{ $t->masa_berlaku->diffForHumans() }}</div>
              </div>
              <span class="badge shrink-0" style="background:var(--amber-100); color:var(--amber-700); border-color:var(--amber-200)">Akan Habis</span>
            </div>
          @empty
          @endforelse
        </div>
        <div class="flex gap-2 mt-4">
          <span class="badge" style="background:var(--red-50); color:var(--red-600); border-color:var(--red-100)">{{ $sertifAkts['expired'] }} habis</span>
          <span class="badge" style="background:var(--amber-50); color:var(--amber-600); border-color:var(--amber-100)">{{ $sertifAkts['expiring'] }} ≤ 120 hari</span>
        </div>
      @endif
    </div>

    <div class="card lg:col-span-2 flex flex-col">
      <div class="flex items-center justify-between px-6 pt-5 pb-4">
        <div class="flex items-center gap-2.5">
          <h3 class="section-title">Pengajuan Menunggu Approval</h3>
          @if($pendingApprovals > 0)
            <span class="badge" style="background:var(--amber-50); color:var(--amber-600); border-color:var(--amber-100)">{{ $pendingApprovals }} menunggu</span>
          @endif
        </div>
        <a href="{{ route('approvals.index') }}" class="text-[12px] font-semibold whitespace-nowrap" style="color:var(--blue-600)">Lihat Semua →</a>
      </div>
      <div class="px-6 pb-6 flex-1">
        @if($recentRequests->isEmpty())
          <div class="empty-state">
            <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Tidak Ada Pengajuan Pending</p>
            <p class="text-[12.5px] mt-1">Semua pengajuan sudah diproses.</p>
          </div>
        @else
          <div class="divide-y" style="border:1px solid var(--line-soft); border-radius:12px; overflow:hidden">
            @foreach ($recentRequests as $cr)
              <div class="flex items-center justify-between gap-3 px-4 py-3 hrow">
                <div class="flex items-center gap-3 min-w-0">
                  <span class="w-9 h-9 rounded-full flex items-center justify-center flex-none" style="background:var(--blue-50); color:var(--blue-600)">
                    <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                  </span>
                  <div class="min-w-0">
                    <div class="text-[13px] font-semibold truncate">{{ $cr->employee->nama_lengkap }}</div>
                    <div class="text-[11.5px] capitalize truncate" style="color:var(--ink-500)">Perubahan {{ str_replace('_', ' ', $cr->module_type) }} · {{ $cr->created_at->translatedFormat('d M Y, H:i') }}</div>
                  </div>
                </div>
                <a href="{{ route('approvals.show', $cr) }}" class="btn btn-outline btn-sm shrink-0">Review</a>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Aktivitas Sistem --}}
  <div class="card p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="section-title">Aktivitas Sistem</h3>
      <a href="{{ route('audit.index') }}" class="text-[12px] font-semibold" style="color:var(--blue-600)">Audit Log →</a>
    </div>
    @if($recentLogs->isEmpty())
      <div class="empty-state"><p class="text-[12.5px]">Belum ada aktivitas tercatat.</p></div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
        @foreach ($recentLogs as $log)
          <div class="flex items-center gap-2.5">
            <span class="badge shrink-0" style="background:var(--blue-50); color:var(--blue-600); border-color:var(--blue-100)">{{ strtoupper($log->action) }}</span>
            <span class="text-[12px] truncate flex-1" style="color:var(--ink-700)">{{ $log->description }}</span>
            <span class="text-[10.5px] shrink-0" style="color:var(--ink-300)">{{ $log->created_at->diffForHumans() }}</span>
          </div>
        @endforeach
      </div>
    @endif
  </div>
@endif
@endsection

<script>
  // Sapaan mengikuti waktu (pagí/siang/sore/malam) — sama seperti halaman login
  (function () {
    var h = new Date().getHours();
    var g = h < 11 ? 'Selamat Pagi' : h < 15 ? 'Selamat Siang' : h < 19 ? 'Selamat Sore' : 'Selamat Malam';
    var el = document.getElementById('greet');
    if (el) el.textContent = g + ',';
  })();
</script>