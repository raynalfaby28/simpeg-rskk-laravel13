@extends('layouts.app')
@section('title', 'Laporan')
@section('crumb', 'Administrasi')
@section('nav-reports', 'active')

@section('content')
@php
  $sections = [
    'pegawai' => 'Data Pegawai', 'unit' => 'Unit Kerja', 'jabatan' => 'Jabatan',
    'golongan' => 'Golongan', 'pendidikan' => 'Pendidikan',
    'status' => 'Status Pegawai', 'dokumen' => 'Dokumen',
    'diklat' => 'Diklat & Sertifikasi', 'kinerja' => 'Kinerja',
  ];
@endphp

<div class="page-head no-print">
  <div>
    <h1 class="page-title">Laporan</h1>
    <p class="page-desc">Rekap data kepegawaian — bisa dicetak langsung atau diexport CSV.</p>
  </div>
  <div class="flex gap-2">
    <button onclick="window.print()" class="btn btn-outline">
      <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2z"/></svg>
      Cetak
    </button>
    <a href="{{ route('reports.csv', $section) }}" class="btn btn-primary">
      <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
      Export CSV
    </a>
  </div>
</div>

{{-- Ringkasan --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6 no-print">
  <div class="stat-card"><div class="text-[20px] font-bold" style="color:var(--blue-600)">{{ $counts['pegawai'] }}</div><div class="text-[11.5px] font-medium mt-1" style="color:var(--ink-500)">Total Pegawai</div></div>
  <div class="stat-card"><div class="text-[20px] font-bold" style="color:var(--green-600)">{{ $counts['aktif'] }}</div><div class="text-[11.5px] font-medium mt-1" style="color:var(--ink-500)">Pegawai Aktif</div></div>
  <div class="stat-card"><div class="text-[20px] font-bold" style="color:var(--teal-600)">{{ $counts['unit'] }}</div><div class="text-[11.5px] font-medium mt-1" style="color:var(--ink-500)">Unit Kerja</div></div>
  <div class="stat-card"><div class="text-[20px] font-bold" style="color:var(--amber-600)">{{ $counts['dokumen'] }}</div><div class="text-[11.5px] font-medium mt-1" style="color:var(--ink-500)">Dokumen</div></div>
</div>

{{-- Pilih bagian --}}
<div class="flex gap-1.5 flex-wrap mb-6 no-print">
  @foreach($sections as $key => $label)
    <a href="{{ route('reports.index', ['section' => $key]) }}" class="chip {{ $section === $key ? 'active' : '' }}">{{ $label }}</a>
  @endforeach
</div>

{{-- Header laporan (cetak) --}}
<div class="hidden print:block mb-4">
  <div class="text-[16px] font-bold">{{ App\Models\Settings::get('app_name', 'SIMPEG RSKK') }}</div>
  <div class="text-[12.5px]">{{ App\Models\Settings::get('rs_name', 'RSUD Kesehatan Kerja') }}</div>
  <div class="text-[12.5px]">{{ App\Models\Settings::get('rs_address', '') }}</div>
  <hr style="border-color:#000; margin:10px 0">
  <div class="text-[14px] font-semibold">Laporan — {{ $sections[$section] }}</div>
  <div class="text-[11.5px]">Dicetak {{ now()->format('d M Y H:i') }} oleh {{ auth()->user()->name }}</div>
</div>

<div class="card overflow-hidden">
  <div class="px-6 py-4 flex items-center justify-between flex-wrap gap-2 no-print" style="border-bottom:1px solid var(--line-soft)">
    <h3 class="section-title">{{ $sections[$section] }}</h3>
    @if($section === 'pegawai')
      <form method="GET" class="flex gap-2 flex-wrap">
        <input type="hidden" name="section" value="pegawai">
        <select name="unit" class="input !w-auto !py-1.5 text-[12.5px]">
          <option value="">Semua Unit</option>
          @foreach($units as $id => $name)
            <option value="{{ $id }}" @selected((string) $unitId === (string) $id)>{{ $name }}</option>
          @endforeach
        </select>
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari NIP / nama..." class="input !w-48 !py-1.5 text-[12.5px]">
        <button class="btn-outline px-3 py-1.5 rounded-lg text-[12px] font-medium">Filter</button>
      </form>
    @elseif(in_array($section, ['diklat', 'kinerja']))
      <form method="GET" class="flex gap-2 flex-wrap">
        <input type="hidden" name="section" value="{{ $section }}">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari NIP / nama / pelatihan..." class="input !w-56 !py-1.5 text-[12.5px]">
        <button class="btn-outline px-3 py-1.5 rounded-lg text-[12px] font-medium">Filter</button>
      </form>
    @endif
    <span class="text-[11.5px]" style="color:var(--ink-300)">{{ $entryCount }} entri</span>
  </div>

  @if($section === 'diklat')
      <div class="px-6 py-4 grid grid-cols-2 sm:grid-cols-4 gap-3 no-print" style="border-bottom:1px solid var(--line-soft)">
        <div class="stat-card"><div class="text-[18px] font-bold" style="color:var(--red-600)">{{ $sertifSummary['expired'] }}</div><div class="text-[11.5px] font-medium mt-1" style="color:var(--ink-500)">Sudah Habis</div></div>
        <div class="stat-card"><div class="text-[18px] font-bold" style="color:var(--amber-600)">{{ $sertifSummary['expiring'] }}</div><div class="text-[11.5px] font-medium mt-1" style="color:var(--ink-500)">Habis ≤ 120 hari</div></div>
        <div class="stat-card"><div class="text-[18px] font-bold" style="color:var(--green-600)">{{ $sertifSummary['valid'] }}</div><div class="text-[11.5px] font-medium mt-1" style="color:var(--ink-500)">Masih Berlaku</div></div>
        <div class="stat-card"><div class="text-[18px] font-bold" style="color:var(--ink-500)">{{ $sertifSummary['tanpa_berlaku'] }}</div><div class="text-[11.5px] font-medium mt-1" style="color:var(--ink-500)">Tanpa Masa Berlaku</div></div>
      </div>
    @elseif($section === 'kinerja' && $kinerjaStats->isNotEmpty())
      <div class="px-6 py-4 flex items-center gap-4 flex-wrap no-print" style="border-bottom:1px solid var(--line-soft)">
        <span class="text-[12px] font-semibold" style="color:var(--ink-700)">Sebaran Predikat:</span>
        @foreach($kinerjaStats as $predikat => $count)
          <span class="flex items-center gap-1.5 text-[12px]" style="color:var(--ink-700)">
            {{ $predikat }} <span class="font-bold" style="color:var(--blue-600)">{{ $count }}</span>
          </span>
        @endforeach
      </div>
    @endif

  <div class="table-wrap">
    @if($section === 'pegawai')
      <table class="w-full min-w-[720px]">
        <thead><tr>
          <th class="table-th">NIP</th><th class="table-th">Nama Lengkap</th><th class="table-th">Jabatan</th>
          <th class="table-th">Unit Kerja</th><th class="table-th">Golongan</th><th class="table-th">Status</th>
        </tr></thead>
        <tbody>
          @forelse($employees as $e)
            <tr class="row-line">
              <td class="table-td" style="color:var(--ink-500)">{{ $e->nip }}</td>
              <td class="table-td font-medium">{{ $e->nama_lengkap }}</td>
              <td class="table-td">{{ $e->currentPosition?->name ?? '—' }}</td>
              <td class="table-td">{{ $e->workUnit?->name ?? '—' }}</td>
              <td class="table-td">{{ $e->golonganAkhir?->golongan ?? '—' }}</td>
              <td class="table-td"><span class="badge" style="background:var(--green-50); color:var(--green-600); border-color:var(--green-100)">{{ $e->status_pegawai ?? '—' }}</span></td>
            </tr>
          @empty
            <tr><td colspan="6" class="table-td text-center py-10" style="color:var(--ink-500)">Belum ada data pegawai.</td></tr>
          @endforelse
        </tbody>
      </table>

    @elseif($section === 'dokumen')
      <table class="w-full min-w-[720px]">
        <thead><tr>
          <th class="table-th">NIP</th><th class="table-th">Nama</th><th class="table-th">Jenis Dokumen</th>
          <th class="table-th">Nomor</th><th class="table-th">Tanggal</th><th class="table-th">Status</th>
        </tr></thead>
        <tbody>
          @forelse($documents as $d)
            <tr class="row-line">
              <td class="table-td" style="color:var(--ink-500)">{{ $d->employee?->nip ?? '—' }}</td>
              <td class="table-td font-medium">{{ $d->employee?->nama_lengkap ?? '—' }}</td>
              <td class="table-td">{{ $d->jenis_dokumen }}</td>
              <td class="table-td">{{ $d->no_dokumen ?? '—' }}</td>
              <td class="table-td">{{ optional($d->tanggal)->format('d M Y') ?? '—' }}</td>
              <td class="table-td"><span class="badge" style="background:var(--blue-50); color:var(--blue-600); border-color:var(--blue-100)">{{ str_replace('_',' ',$d->status_verifikasi) }}</span></td>
            </tr>
          @empty
            <tr><td colspan="6" class="table-td text-center py-10" style="color:var(--ink-500)">Belum ada dokumen.</td></tr>
          @endforelse
        </tbody>
      </table>

    @elseif($section === 'diklat')
      <table class="w-full min-w-[820px]">
        <thead><tr>
          <th class="table-th">NIP</th><th class="table-th">Nama</th><th class="table-th">Pelatihan / Sertifikasi</th>
          <th class="table-th">Jenis</th><th class="table-th">Selesai</th><th class="table-th">Masa Berlaku</th><th class="table-th">Status</th>
        </tr></thead>
        <tbody>
          @forelse($trainings as $t)
            @php
              $mb = $t->masa_berlaku;
              if ($mb) {
                if ($mb < now()) { $st = ['Habis', 'var(--red-50)', 'var(--red-600)', 'var(--red-100)']; }
                elseif ($mb <= now()->addDays(120)) { $st = ['Akan Habis', 'var(--amber-50)', 'var(--amber-600)', 'var(--amber-100)']; }
                else { $st = ['Berlaku', 'var(--green-50)', 'var(--green-600)', 'var(--green-100)']; }
              } else { $st = ['—', 'var(--line-soft)', 'var(--ink-500)', 'var(--line)']; }
            @endphp
            <tr class="row-line">
              <td class="table-td" style="color:var(--ink-500)">{{ $t->employee?->nip ?? '—' }}</td>
              <td class="table-td font-medium">{{ $t->employee?->nama_lengkap ?? '—' }}</td>
              <td class="table-td">{{ $t->nama_pelatihan }}</td>
              <td class="table-td">{{ $t->jenis_pelatihan ?? '—' }}</td>
              <td class="table-td">{{ optional($t->tanggal_selesai)->format('d M Y') ?? '—' }}</td>
              <td class="table-td">{{ optional($mb)->format('d M Y') ?? '—' }}</td>
              <td class="table-td"><span class="badge" style="background:{{ $st[1] }}; color:{{ $st[2] }}; border-color:{{ $st[3] }}">{{ $st[0] }}</span></td>
            </tr>
          @empty
            <tr><td colspan="7" class="table-td text-center py-10" style="color:var(--ink-500)">Belum ada data diklat & sertifikasi.</td></tr>
          @endforelse
        </tbody>
      </table>

    @elseif($section === 'kinerja')
      <table class="w-full min-w-[760px]">
        <thead><tr>
          <th class="table-th">NIP</th><th class="table-th">Nama</th><th class="table-th">Tahun</th>
          <th class="table-th">Periode</th><th class="table-th">Nilai</th><th class="table-th">Predikat</th>
        </tr></thead>
        <tbody>
          @forelse($performances as $p)
            <tr class="row-line">
              <td class="table-td" style="color:var(--ink-500)">{{ $p->employee?->nip ?? '—' }}</td>
              <td class="table-td font-medium">{{ $p->employee?->nama_lengkap ?? '—' }}</td>
              <td class="table-td">{{ $p->tahun ?? '—' }}</td>
              <td class="table-td">{{ $p->periode ?? '—' }}</td>
              <td class="table-td font-semibold">{{ $p->nilai }}</td>
              <td class="table-td"><span class="badge" style="background:var(--blue-50); color:var(--blue-600); border-color:var(--blue-100)">{{ $p->predikat ?? '—' }}</span></td>
            </tr>
          @empty
            <tr><td colspan="6" class="table-td text-center py-10" style="color:var(--ink-500)">Belum ada data kinerja.</td></tr>
          @endforelse
        </tbody>
      </table>

    @else
      @php
        $data = ['unit' => $perUnit, 'jabatan' => $perPosition, 'golongan' => $perRank,
                 'pendidikan' => $perEducation, 'status' => $perStatus];
        $rows = $data[$section] ?? [];
        $maxVal = $rows->first() ?? 1;
      @endphp
      <table class="w-full min-w-[480px]">
        <thead><tr>
          <th class="table-th">#</th>
          <th class="table-th">Kelompok</th>
          <th class="table-th">Jumlah Pegawai</th>
        </tr></thead>
        <tbody>
          @forelse($rows as $label => $count)
            <tr class="row-line">
              <td class="table-td" style="color:var(--ink-300)">{{ $loop->iteration }}</td>
              <td class="table-td font-medium">{{ $label }}</td>
              <td class="table-td">
                <div class="flex items-center gap-3">
                  <div class="progress w-40"><div style="width:{{ round($count / $maxVal * 100) }}%; background:linear-gradient(90deg, var(--teal-500), var(--blue-600))"></div></div>
                  <span class="text-[12.5px] font-semibold">{{ $count }}</span>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="table-td text-center py-10" style="color:var(--ink-500)">Belum ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
    @endif
  </div>
</div>
@endsection