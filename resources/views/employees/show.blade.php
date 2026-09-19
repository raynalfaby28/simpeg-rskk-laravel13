@extends('layouts.app')
@section('title', $employee->nama_lengkap)
@section('crumb', 'Profil Pegawai')
@section('nav-employees', 'active')

@section('content')
<style>
  .card .field-row{ border-bottom:none; padding:3px 0; }
  /* Tombol "Lainnya" — dropdown di ujung tab bar */
  .tab-more{ display:inline-flex; align-items:center; gap:7px; padding:7px 12px; margin:8px 2px 8px 6px;
             border-radius:10px; font-size:13px; font-weight:600; color:var(--ink-700); background:#fff;
             border:1px solid var(--line); transition:.15s; white-space:nowrap; cursor:pointer; }
  .tab-more:hover{ border-color:var(--blue-600); color:var(--blue-600); background:var(--blue-50); }
  .tab-more .chev{ color:var(--ink-300); margin-left:1px; transition:color .15s; }
  .tab-more:hover .chev{ color:var(--blue-600); }
  .dropdown.open .tab-more{ border-color:var(--blue-600); color:var(--blue-600); background:var(--blue-50); }
  #dd-tabs-more .more-menu{ position:absolute; top:calc(100% + 13px); right:-8px; width:318px; background:#fff;
    border:1px solid var(--line); border-radius:16px;
    box-shadow:0 18px 44px rgba(16,24,40,.14), 0 4px 12px rgba(16,24,40,.08); padding:8px;
    opacity:0; transform:translateY(-8px) scale(.97); pointer-events:none;
    transform-origin:top right; transition:.18s cubic-bezier(.2,.7,.3,1); }
  #dd-tabs-more.dropdown.open .more-menu{ opacity:1; transform:translateY(0) scale(1); pointer-events:auto; }
  #dd-tabs-more .more-caret{ position:absolute; top:-6px; right:26px; width:12px; height:12px; background:#fff;
    border-left:1px solid var(--line); border-top:1px solid var(--line); transform:rotate(45deg); border-radius:2.5px 0 0 0; }
  #dd-tabs-more .more-head{ display:flex; align-items:center; gap:11px; padding:9px 10px 12px; border-bottom:1px solid var(--line-soft); }
  #dd-tabs-more .more-ic{ width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg,var(--blue-600),var(--blue-500)); color:#fff; box-shadow:0 4px 10px rgba(37,99,235,.25); }
  #dd-tabs-more .more-ic svg{ width:17px; height:17px; }
  #dd-tabs-more .more-title{ font-family:var(--font-head); font-size:13.5px; font-weight:700; color:var(--ink-900); }
  #dd-tabs-more .more-sub{ font-size:11px; color:var(--ink-400); margin-top:1px; }
  #dd-tabs-more .more-scroll{ padding:6px 0 2px; }
  #dd-tabs-more .more-item{ display:flex; align-items:center; gap:12px; width:100%; text-align:left; padding:9px 10px;
    border-radius:10px; color:var(--ink-700); transition:background .14s; cursor:pointer; border:0; background:transparent; }
  #dd-tabs-more .more-item:hover{ background:var(--blue-50); }
  #dd-tabs-more .more-item .mi-ic{ width:34px; height:34px; flex:none; border-radius:9px; display:flex; align-items:center;
    justify-content:center; background:var(--blue-50); color:var(--blue-600); transition:.14s; }
  #dd-tabs-more .more-item .mi-ic svg{ width:16px; height:16px; }
  #dd-tabs-more .more-item:hover .mi-ic{ background:var(--blue-100); }
  #dd-tabs-more .mi-tx{ min-width:0; flex:1; display:flex; flex-direction:column; }
  #dd-tabs-more .mi-name{ font-size:13px; font-weight:600; color:var(--ink-800); }
  #dd-tabs-more .mi-sub{ font-size:11px; color:var(--ink-400); margin-top:1px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  #dd-tabs-more .more-item.active{ background:var(--blue-50); }
  #dd-tabs-more .more-item.active .mi-ic{ background:var(--blue-600); color:#fff; box-shadow:0 3px 8px rgba(37,99,235,.3); }
  #dd-tabs-more .more-item.active .mi-name{ color:var(--blue-700); }
  #dd-tabs-more .more-item.active .m-chek{ display:inline-flex !important; color:var(--blue-600); }
  #dd-tabs-more .more-div{ height:1px; background:var(--line-soft); margin:6px 10px; }
</style>
@php
  $isSA = auth()->user()->role === 'super_admin';
  $isAdmin = in_array(auth()->user()->role, ['super_admin', 'admin']);
  $st = $employee->status_pegawai ?? ($employee->employmentStatus?->name ?? null);
  $isActive = $st ? stripos($st, 'aktif') !== false : false;
  $completenessFields = ['tempat_lahir','tanggal_lahir','jenis_kelamin','agama','nik','hp','email_resmi','no_kk','status_perkawinan','no_npwp'];
  $completeness = round(collect($completenessFields)->filter(fn ($f) => filled($employee->{$f}))->count() / count($completenessFields) * 100);

  $eduFormal   = $employee->educations->where('kategori', '!=', 'non_formal');
  $eduNonFormal = $employee->educations->where('kategori', 'non_formal');

  $diklatCat = [
      'struktural' => 'Diklat Struktural',
      'fungsional' => 'Diklat Fungsional',
      'teknis' => 'Diklat Teknis',
      'keahlian_profesi' => 'Sertifikat Keahlian / Profesi',
      'bintek_seminar' => 'Bimbingan Teknis / Seminar',
      'lainnya' => 'Diklat Lainnya',
  ];
  $diklatGroups = [];
  foreach ($diklatCat as $k => $label) {
      $items = $k === 'lainnya'
          ? $employee->trainings->filter(fn ($t) => !in_array($t->kategori, array_keys($diklatCat), true) || $t->kategori === 'lainnya')
          : $employee->trainings->where('kategori', $k);
      if ($items->isNotEmpty()) { $diklatGroups[$k] = ['label' => $label, 'items' => $items]; }
  }

  $docGrups = \App\Http\Controllers\DocumentController::KATEGORI_GRUP;
  $docsByCat = $employee->documents->groupBy(fn ($d) => $d->kategori ?: 'lainnya');
@endphp

<div class="page-head no-print mb-6">
  <div>
    <h1 class="page-title">Profil Pegawai</h1>
    <p class="page-desc">Informasi lengkap pegawai beserta riwayat, dokumen, dan status kepegawaian.</p>
  </div>
  <div class="flex items-center gap-2">
    <a href="{{ route('employees.index') }}" class="btn btn-outline">
      <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Kembali
    </a>
    <button onclick="window.print()" class="btn btn-outline">
      <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2z"/></svg>
      Print
    </button>
    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">
      <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9a4.2 4.2 0 10-5.9 5.9L19 13l6-6-3.6-3.6z"/></svg>
      @if($employee->is_draft) Lanjutkan Isi @else Edit Profil @endif
    </a>
  </div>
</div>

{{-- PROFILE HEADER --}}
<div class="card overflow-hidden mb-6 no-print">
  <div class="h-16 relative" style="background:linear-gradient(90deg,var(--navy-800),var(--blue-700));">
    <div class="absolute right-5 bottom-2 text-[10px] font-medium uppercase tracking-wider" style="color:rgba(255,255,255,.45)">Profil Pegawai · {{ $employee->nip ?? '' }}</div>
  </div>
  <div class="px-6 pb-5 pt-4">
    <div class="flex flex-wrap items-start gap-5">
      <div class="profile-avatar-wrap">
        <div class="profile-avatar {{ $employee->foto_path ? 'image-present' : '' }}">
          @if($employee->foto_path)
            <img src="{{ asset('storage/' . $employee->foto_path) }}" class="avatar-zoom" onclick="openPhotoViewer('{{ asset('storage/' . $employee->foto_path) }}')" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" alt="{{ $employee->nama_lengkap }}" title="Perbesar foto">
          @else
            <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->nama_lengkap ?: 'Draft') }}&background=2563EB&color=fff&size=120" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" alt="{{ $employee->nama_lengkap }}">
          @endif
          <span class="pa-inits" style="display:{{ $employee->foto_path ? 'none' : 'flex' }}">{{ mb_strtoupper(mb_substr($employee->nama_lengkap ?: 'P', 0, 1)) }}</span>
        </div>
        @if(auth()->user()->role !== 'user')
          <button type="button" class="avatar-edit" onclick="document.getElementById('foto-pegawai-input').click()" title="Ganti Foto">
            <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.9a2 2 0 001.7-1l.6-1.2A2 2 0 017.9 4h8.2a2 2 0 011.7 1l.6 1.2a2 2 0 001.7 1H19a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </button>
          <form id="foto-pegawai-form" method="POST" action="{{ route('employees.photo', $employee) }}" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="file" id="foto-pegawai-input" name="foto" accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden" onchange="this.form.submit()">
          </form>
        @endif
      </div>
      <div class="flex-1 min-w-48 pt-0.5">
        <div class="flex flex-wrap items-center gap-2">
          <h1 class="text-[20px] font-bold leading-tight" style="color:var(--navy-900)">{{ $employee->nama_lengkap_dengan_gelar ?: '(Belum Ada Nama)' }}</h1>
          @if($employee->is_draft)
            <span class="badge" style="background:var(--amber-100); color:var(--amber-800); border-color:var(--amber-200)">
              <span class="dot" style="background:var(--amber-600)"></span>Draft
            </span>
          @endif
          @if($st)
            <span class="badge" style="background:{{ $isActive ? 'var(--green-50)' : 'var(--amber-50)' }}; color:{{ $isActive ? 'var(--green-600)' : 'var(--amber-600)' }}; border-color:{{ $isActive ? 'var(--green-100)' : 'var(--amber-100)' }}">
              <span class="dot" style="background:{{ $isActive ? 'var(--green-600)' : 'var(--amber-600)' }}"></span>{{ $st }}
            </span>
          @endif
        </div>
        <div class="text-[12px] mt-1" style="font-family:ui-monospace,monospace;color:var(--ink-500)">NIP. {{ $employee->nip ?? '' }}</div>
        <div class="text-[13px] mt-1 font-medium" style="color:var(--ink-700)">{{ $employee->currentPosition?->name }}</div>
        <div class="text-[13px] mt-0.5" style="color:var(--ink-700)">{{ $employee->workUnit?->name }}</div>
      </div>
      <div class="p-4 rounded-xl flex-none" style="border:1px solid var(--line-soft);background:#fff;min-width:190px;max-width:240px">
        <div class="field-row" style="padding:5px 0"><span style="color:var(--ink-500)">Unit Kerja</span><span class="text-right font-medium" style="color:var(--ink-700);max-width:130px">{{ $employee->workUnit?->name }}</span></div>
        <div class="field-row" style="padding:5px 0"><span style="color:var(--ink-500)">Jabatan</span><span class="text-right font-medium" style="color:var(--ink-700);max-width:130px">{{ $employee->currentPosition?->name }}</span></div>
        <div class="field-row" style="padding:5px 0;border:none"><span style="color:var(--ink-500)">Pangkat/Golongan</span><span class="text-right font-medium" style="color:var(--ink-700)">{{ $employee->golonganAkhir ? $employee->golonganAkhir->golongan . ($employee->golonganAkhir->pangkat ? ' (' . $employee->golonganAkhir->pangkat . ')' : '') : '' }}</span></div>
      </div>
    </div>
    <div class="flex gap-2 mt-4">
      <button onclick="window.print()" class="btn btn-outline btn-sm" style="padding:6px 14px;font-size:12px">
        <svg style="width:13px;height:13px;display:inline;margin-right:4px;vertical-align:-1px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2z"/></svg>
        Cetak
      </button>
      <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary btn-sm" style="padding:6px 14px;font-size:12px">
        @if($employee->is_draft) Lanjutkan Isi @else Edit Profil @endif
      </a>
    </div>
    @if($employee->is_draft)
      <div class="mt-4 px-4 py-3 rounded-xl flex items-center gap-3" style="background:var(--amber-50); border:1px solid var(--amber-100)">
        <svg style="width:18px;height:18px;flex:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" color="#B54708"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
        <div class="text-[13px] flex-1" style="color:var(--amber-800)">
          <strong>Data masih berupa draft.</strong> Pegawai belum resmi tersimpan. Lengkapi & simpan final melalui tombol di atas.
        </div>
        <a href="{{ route('employees.edit', $employee) }}" class="btn-outline px-3 py-1.5 rounded-lg text-[12px] font-semibold" style="border-color:var(--amber-200)">Lanjutkan Isi</a>
      </div>
    @endif
  </div>
</div>

{{-- TABS + CONTENT --}}
<div class="card overflow-hidden">
  <div class="flex items-center px-2 border-b" style="border-color:var(--line)">
    <div class="flex items-center overflow-x-auto">
      <button data-tab="ringkasan" class="tab-btn active">Ringkasan</button>
      <button data-tab="pribadi" class="tab-btn">Data Pribadi</button>
      <button data-tab="dokumen" class="tab-btn">Arsip & Dokumen Digital</button>
      <button data-tab="kepegawaian" class="tab-btn">Kepegawaian</button>
    </div>
    <div style="flex:1"></div>
    <div class="dropdown flex-none no-print" id="dd-tabs-more" style="margin-right:6px">
      <button type="button" class="tab-more" onclick="toggleDropdown('dd-tabs-more'); return false;" aria-haspopup="true" aria-expanded="false">
        <span id="tab-more-icon">
          <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
        </span>
        <span id="tab-more-label">Lainnya</span>
        <svg class="chev" style="width:13px;height:13px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="more-menu">
        <div class="more-caret"></div>
        <div class="more-head">
          <span class="more-ic">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
          </span>
          <div>
            <div class="more-title">Menu Profil</div>
            <div class="more-sub">Semua bagian data pegawai</div>
          </div>
        </div>
        <div class="more-scroll">
          <button type="button" class="menu-item more-item" data-tab="pendidikan-diklat" onclick="pickMore(this)">
            <span class="mi-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.7 14.9L3 18.5V6.5l6.7-3.6L16.3 6.5v6M9.7 3.9V14m0 0l6.6-3.5M16.3 11.2l4.7-2.5V6.5m-4.7 9.2l4.7-2.5"/></svg></span>
            <span class="mi-tx"><span class="mi-name">Pendidikan &amp; Diklat</span><span class="mi-sub">Ijazah, pelatihan, dan sertifikasi</span></span>
            <svg class="m-chek" style="width:15px;height:15px;margin-left:auto;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          </button>
          <button type="button" class="menu-item more-item" data-tab="kinerja-penghargaan" onclick="pickMore(this)">
            <span class="mi-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.5 3.6L13.9 8l4.9.7-3.5 3.4.8 4.8-4.4-2.3-4.4 2.3.8-4.8-3.5-3.4 4.9-.7 2.4-4.4z"/></svg></span>
            <span class="mi-tx"><span class="mi-name">Kinerja &amp; Penghargaan</span><span class="mi-sub">SKP, penilaian, dan prestasi</span></span>
            <svg class="m-chek" style="width:15px;height:15px;margin-left:auto;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          </button>
          <button type="button" class="menu-item more-item" data-tab="keluarga" onclick="pickMore(this)">
            <span class="mi-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.9M9 20H4v-2a4 4 0 013-3.9m6-1.6a4 4 0 10-4-4m8 0a4 4 0 11-4-4"/></svg></span>
            <span class="mi-tx"><span class="mi-name">Keluarga</span><span class="mi-sub">Pasangan, anak, dan orang tua</span></span>
            <svg class="m-chek" style="width:15px;height:15px;margin-left:auto;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          </button>
          <button type="button" class="menu-item more-item" data-tab="aset" onclick="pickMore(this)">
            <span class="mi-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7M4 7l3-3m-3 3h16M17 4l3 3M8 21h8"/></svg></span>
            <span class="mi-tx"><span class="mi-name">Aset Pegawai</span><span class="mi-sub">Barang milik yang dikuasai</span></span>
            <svg class="m-chek" style="width:15px;height:15px;margin-left:auto;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          </button>
          <div class="more-div"></div>
          <button type="button" class="menu-item more-item" data-tab="riwayat-perubahan" onclick="pickMore(this)">
            <span class="mi-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
            <span class="mi-tx"><span class="mi-name">Riwayat Perubahan</span><span class="mi-sub">Log pengajuan dan persetujuan</span></span>
            <svg class="m-chek" style="width:15px;height:15px;margin-left:auto;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="p-6">

    {{-- ===== TAB: RINGKASAN ===== --}}
    <div class="tab-panel" id="tab-ringkasan">
      <div class="mb-5">
        <h3 class="section-title">Ringkasan</h3>
        <p class="text-[12.5px] mt-1" style="color:var(--ink-500)">Informasi umum mengenai data pegawai.</p>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-3" style="padding-bottom:10px;border-bottom:1px solid var(--line-soft)">Informasi Pribadi</h3>
          @include('employees._kv', ['items' => [
            'Nama Lengkap' => $employee->nama_lengkap_dengan_gelar ?: $employee->nama_lengkap,
            'NIP' => $employee->nip, 'NIK' => $employee->nik,
            'Tempat/Tanggal Lahir' => trim(($employee->tempat_lahir ?? '') . ', ' . ($employee->tanggal_lahir?->format('d M Y') ?? '')),
            'Jenis Kelamin' => $employee->jenis_kelamin === 'P' ? 'Perempuan' : ($employee->jenis_kelamin === 'L' ? 'Laki-laki' : null),
            'Agama' => $employee->agama, 'Status Perkawinan' => $employee->status_perkawinan, 'Golongan Darah' => $employee->golongan_darah,
            'Nama Ibu Kandung' => $employee->nama_ibu_kandung,
          ]])
        </div>
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-3" style="padding-bottom:10px;border-bottom:1px solid var(--line-soft)">Informasi Kepegawaian</h3>
          @include('employees._kv', ['items' => [
            'Status Pegawai' => $employee->status_pegawai,
            'Kategori Pegawai' => $employee->employeeCategory?->name ?? $employee->employmentStatus?->name,
            'Jenis ASN' => $employee->jenis_asn,
            'Unit Kerja' => $employee->workUnit?->name,
            'Jabatan' => $employee->currentPosition?->name,
            'Jenis Jabatan' => ucfirst($employee->jenis_jabatan ?? ''),
            'Pangkat/Golongan' => $employee->golonganAkhir ? $employee->golonganAkhir->golongan . ($employee->golonganAkhir->pangkat ? ' (' . $employee->golonganAkhir->pangkat . ')' : '') : null,
            'TMT Jabatan' => optional($employee->tmt_jabatan)->format('d M Y'),
            'TMT Pangkat' => optional($employee->tmt_golongan_akhir)->format('d M Y'),
            'Masa Kerja' => $employee->masa_kerja_tahun !== null ? $employee->masa_kerja_tahun . ' Tahun ' . ($employee->masa_kerja_bulan ?? 0) . ' Bulan' : null,
          ]])
        </div>
      </div>
    </div>

    {{-- ===== TAB: DATA PRIBADI ===== --}}
    <div class="tab-panel hidden" id="tab-pribadi">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-2">Identitas Pribadi</h3>
          @include('employees._kv', ['items' => [
            'NIP' => $employee->nip, 'NIP Lama' => $employee->nip_lama, 'Nama Lengkap' => $employee->nama_lengkap,
            'Gelar Depan' => $employee->gelar_depan, 'Gelar Belakang' => $employee->gelar_belakang, 'Nama Panggilan' => $employee->nama_panggilan,
            'Tempat, Tanggal Lahir' => trim(($employee->tempat_lahir ?? '') . ', ' . ($employee->tanggal_lahir?->format('d M Y') ?? '')),
            'Jenis Kelamin' => $employee->jenis_kelamin === 'P' ? 'Perempuan' : ($employee->jenis_kelamin === 'L' ? 'Laki-laki' : null),
            'Agama' => $employee->agama, 'Golongan Darah' => $employee->golongan_darah,
          ]])
        </div>
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-2">Kependudukan & Nomor</h3>
          @include('employees._kv', ['items' => [
            'NIK / KTP' => $employee->nik, 'No. KK' => $employee->no_kk, 'NPWP' => $employee->no_npwp,
            'No. BPJS' => $employee->no_bpjs, 'KARIP / KARSU' => $employee->no_karis_karsu, 'KARPEG' => $employee->no_karpeg,
            'No. Taspen' => $employee->no_taspen, 'No. Rekening' => $employee->no_rekening ? ($employee->bank . ' ' . $employee->no_rekening) : null,
            'BA Pertarum' => $employee->bapertarum, 'Status Perkawinan' => $employee->status_perkawinan,
          ]])
        </div>
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-2">Kelahiran</h3>
          @include('employees._kv', ['items' => [
            'Nama Ibu Kandung' => $employee->nama_ibu_kandung,
            'No. Akta Kelahiran' => $employee->no_akta_kelahiran,
          ]])
        </div>
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-2">Pernikahan / Perceraian</h3>
          @include('employees._kv', ['items' => [
            'Status Perkawinan' => $employee->status_perkawinan,
            'No. Buku Nikah' => $employee->no_buku_nikah,
            'No. Akta Cerai' => $employee->no_akta_cerai,
          ]])
        </div>
      </div>

      <h3 class="section-title mb-3 mt-8">Alamat</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-2">Alamat Rumah</h3>
          @include('employees._kv', ['items' => [
            'Alamat' => $employee->alamat_rumah,
            'RT / RW' => trim(($employee->rt_rumah ?? '') . ' / ' . ($employee->rw_rumah ?? '')),
            'Kelurahan' => $employee->kelurahan_rumah, 'Kecamatan' => $employee->kecamatan_rumah,
            'Kab/Kota' => $employee->kabkota_rumah, 'Provinsi' => $employee->provinsi_rumah,
            'Kode Pos' => $employee->kodepos_rumah,
          ]])
        </div>
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-2">Alamat Domisili (KTP)</h3>
          @include('employees._kv', ['items' => [
            'Alamat KTP' => $employee->alamat_domisili_ktp,
            'RT / RW' => trim(($employee->rt_domisili ?? '') . ' / ' . ($employee->rw_domisili ?? '')),
            'Kelurahan' => $employee->kelurahan_domisili, 'Kecamatan' => $employee->kecamatan_domisili,
            'Kab/Kota' => $employee->kabkota_domisili, 'Provinsi' => $employee->provinsi_domisili,
            'Kode Pos' => $employee->kodepos_domisili,
          ]])
        </div>
      </div>

      <h3 class="section-title mb-3 mt-8">Kontak</h3>
      <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
        @include('employees._kv', ['items' => [
          'No. Telepon' => $employee->telp, 'No. HP' => $employee->hp,
          'Email Pribadi' => $employee->email_pribadi, 'Email Resmi' => $employee->email_resmi,
        ]])
      </div>

      @if(!$employee->tempat_lahir)
        <p class="text-[12.5px] mt-5 px-4 py-3 rounded-lg alert alert-warning">
          Profil ini masih kosong. Data baru terisi kalau pegawai bersangkutan (atau Admin) melengkapi lewat form Edit Profil.
        </p>
      @endif
    </div>

    {{-- ===== TAB: ARSIP & DOKUMEN DIGITAL ===== --}}
    <div class="tab-panel hidden" id="tab-dokumen">
      @include('employees._section', [
        'title' => 'Arsip Dokumen Digital',
        'sub' => 'Dokumen dikelompokkan per kategori arsip.',
        'createUrl' => route('documents.create-admin', $employee),
        'createLabel' => 'Unggah Dokumen',
      ])

      @php $docsEmpty = $employee->documents->isEmpty(); @endphp
      @if($docsEmpty)
        <div class="empty-state">
          <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2zM15 3v6h6"/></svg></div>
          <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Belum Ada Dokumen</p>
          <p class="text-[12.5px] mt-1">Belum ada dokumen yang ditambahkan untuk pegawai ini.</p>
        </div>
      @else
        @foreach($docGrups as $gKey => $gLabel)
          @php $docs = $docsByCat->get($gKey, collect()); @endphp
          @if($docs->isNotEmpty())
            <h3 class="section-title mb-3 mt-8 first:mt-0">{{ $gLabel }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
              @foreach($docs as $doc)
                @php
                  $pill = match ($doc->status_verifikasi) {
                    'terverifikasi' => ['bg' => 'var(--green-50)', 'fg' => 'var(--green-600)', 'bd' => 'var(--green-100)', 'txt' => 'Terverifikasi'],
                    'ditolak' => ['bg' => 'var(--red-50)', 'fg' => 'var(--red-600)', 'bd' => 'var(--red-100)', 'txt' => 'Ditolak'],
                    default => ['bg' => 'var(--amber-50)', 'fg' => 'var(--amber-600)', 'bd' => 'var(--amber-100)', 'txt' => 'Perlu Verifikasi'],
                  };
                @endphp
                <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                  <div class="flex items-start justify-between mb-2">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:var(--blue-50); color:var(--blue-600)">
                      <svg style="width:17px;height:17px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2zM15 3v6h6"/></svg>
                    </div>
                    <span class="badge" style="background:{{ $pill['bg'] }}; color:{{ $pill['fg'] }}; border-color:{{ $pill['bd'] }}">{{ $pill['txt'] }}</span>
                  </div>
                  <div class="text-[13px] font-semibold">{{ $doc->jenis_dokumen }}</div>
                  <div class="text-[11px]" style="color:var(--ink-500)">@if($doc->no_dokumen){{ $doc->no_dokumen }} @endif{{ optional($doc->tanggal)->format('d M Y') ?? '' }}</div>
                  @if($doc->status_verifikasi === 'ditolak' && $doc->keterangan)
                    <div class="text-[11.5px] mt-2 px-3 py-2 rounded-lg" style="background:var(--red-50); color:var(--red-600)">{{ $doc->keterangan }}</div>
                  @endif
                  <div class="flex gap-1.5 mt-3 pt-3" style="border-top:1px solid var(--line-soft)">
                    <a href="{{ route('documents.download', $doc) }}" class="btn-outline flex-1 text-center px-3 py-1.5 rounded-lg text-[11.5px] font-medium">Unduh</a>
                    @if($isAdmin)
                      <a href="{{ route('documents.edit', $doc) }}" class="btn-outline text-center px-3 py-1.5 rounded-lg text-[11.5px] font-medium">Edit</a>
                      <form method="POST" action="{{ route('documents.destroy', $doc) }}" onsubmit="return confirm('Hapus dokumen ini?')">
                        @csrf @method('DELETE')
                        <button class="px-3 py-1.5 rounded-lg text-[11.5px] font-semibold" style="color:var(--red-600)">Hapus</button>
                      </form>
                    @endif
                    @if($doc->status_verifikasi !== 'terverifikasi' && $isAdmin)
                      <form method="POST" action="{{ route('documents.verify', $doc) }}">
                        @csrf
                        <button class="px-3 py-1.5 rounded-lg text-[11.5px] font-medium" style="background:var(--green-50); color:var(--green-600)">Verifikasi</button>
                      </form>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        @endforeach
      @endif
    </div>

    {{-- ===== TAB: PENDIDIKAN & DIKLAT ===== --}}
    <div class="tab-panel hidden" id="tab-pendidikan-diklat">

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <div class="flex items-center justify-between mb-4">
            <h3 class="section-title mb-0">Pendidikan Formal</h3>
            <a href="{{ route('sub.create', [$employee, 'pendidikan']) }}" class="btn-outline px-3 py-1.5 rounded-lg text-[12px] font-medium flex-none">+ Tambah</a>
          </div>
          @if($eduFormal->isEmpty())
            <div class="empty-state"><p class="text-[13px]">Belum ada riwayat pendidikan formal.</p></div>
          @else
            <div class="timeline">
              @foreach ($eduFormal->sortByDesc('tahun_masuk') as $edu)
                <div class="tl-item">
                  <span class="tl-dot"></span>
                  <div class="tl-year">{{ $edu->tahun_masuk }} – {{ $edu->tahun_lulus ?? 'sekarang' }}</div>
                  <div class="text-[14px] font-semibold mt-0.5">{{ $edu->educationLevel?->name ?? '' }}</div>
                  <div class="text-[12.5px]" style="color:var(--ink-500)">
                    {{ $edu->institution ?? '' }}{{ $edu->major ? ' · ' . $edu->major : '' }}
                    @if($edu->no_ijazah)<div class="mt-0.5 text-[11.5px]">No. Ijazah: {{ $edu->no_ijazah }}</div>@endif
                  </div>
                  <div class="flex gap-1.5 mt-2 text-[11.5px]">
                    @if($edu->file_ijazah_path)
                      <a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['pendidikan', $edu->id]) }}">Unduh Ijazah</a>
                    @endif
                    <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'pendidikan', $edu->id]) }}">Edit</a>
                    <form method="POST" action="{{ route('sub.destroy', [$employee, 'pendidikan', $edu->id]) }}" onsubmit="return confirm('Hapus riwayat pendidikan ini?')">
                      @csrf @method('DELETE')
                      <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                    </form>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>

        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-4">Pendidikan Non Formal</h3>
          @if($eduNonFormal->isEmpty())
            <div class="empty-state"><p class="text-[13px]">Belum ada pendidikan non formal.</p></div>
          @else
            <div class="timeline">
              @foreach ($eduNonFormal->sortByDesc('tahun_masuk') as $edu)
                <div class="tl-item">
                  <span class="tl-dot"></span>
                  <div class="tl-year">{{ $edu->tahun_masuk }} – {{ $edu->tahun_lulus ?? 'sekarang' }}</div>
                  <div class="text-[14px] font-semibold mt-0.5">{{ $edu->educationLevel?->name ?? '' }}</div>
                  <div class="text-[12.5px]" style="color:var(--ink-500)">
                    {{ $edu->institution ?? '' }}{{ $edu->major ? ' · ' . $edu->major : '' }}
                    @if($edu->no_ijazah)<div class="mt-0.5 text-[11.5px]">No. Ijazah: {{ $edu->no_ijazah }}</div>@endif
                  </div>
                  <div class="flex gap-1.5 mt-2 text-[11.5px]">
                    @if($edu->file_ijazah_path)
                      <a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['pendidikan', $edu->id]) }}">Unduh</a>
                    @endif
                    <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'pendidikan', $edu->id]) }}">Edit</a>
                    <form method="POST" action="{{ route('sub.destroy', [$employee, 'pendidikan', $edu->id]) }}" onsubmit="return confirm('Hapus riwayat pendidikan ini?')">
                      @csrf @method('DELETE')
                      <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                    </form>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>

      <div class="mt-10">
        @include('employees._section', [
          'title' => 'Diklat & Pelatihan',
          'sub' => 'Diklat struktural, fungsional, teknis, sertifikat keahlian/profesi, serta bimbingan teknis & seminar.',
          'createUrl' => route('sub.create', [$employee, 'diklat']),
          'createLabel' => 'Tambah Diklat',
        ])
        @if($employee->trainings->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada diklat tercatat.</p></div>
        @else
          @foreach($diklatGroups as $gIdx => $grp)
            <h3 class="section-title mb-3 mt-7 first:mt-0">{{ $grp['label'] }}</h3>
            <div class="space-y-3 mb-5">
              @foreach ($grp['items']->sortByDesc('tanggal_selesai') as $t)
                <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                  <div class="flex items-start justify-between gap-2">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-none" style="background:var(--teal-50); color:var(--teal-600)">🎓</div>
                    <div class="min-w-0 flex-1">
                      <div class="text-[13.5px] font-semibold">{{ $t->nama_pelatihan }}</div>
                      <div class="text-[12px]" style="color:var(--ink-500)">{{ $t->penyelenggara ?? '' }} · {{ optional($t->tanggal_mulai)->format('M Y') ?? '' }}{{ $t->tanggal_selesai ? ' – ' . optional($t->tanggal_selesai)->format('M Y') : '' }}</div>
                      @if($t->no_sertifikat)<div class="text-[11.5px] mt-1" style="color:var(--ink-500)">Sertifikat: {{ $t->no_sertifikat }}</div>@endif
                      <div class="flex gap-2 mt-2 text-[11.5px]">
                        @if($t->file_sertifikat_path)
                          <a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['diklat', $t->id]) }}">Unduh Sertifikat</a>
                        @endif
                        <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'diklat', $t->id]) }}">Edit</a>
                        <form method="POST" action="{{ route('sub.destroy', [$employee, 'diklat', $t->id]) }}" onsubmit="return confirm('Hapus diklat ini?')">
                          @csrf @method('DELETE')
                          <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @endforeach
        @endif
      </div>

      <div class="mt-2">
        @include('employees._section', [
          'title' => 'Riwayat Bahasa',
          'sub' => 'Bahasa yang dikuasai pegawai beserta tingkat & kemampuan.',
          'createUrl' => route('sub.create', [$employee, 'bahasa']),
          'createLabel' => 'Tambah Bahasa',
        ])
        @if($employee->languages->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada riwayat bahasa.</p></div>
        @else
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($employee->languages as $lang)
              <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                <div class="flex items-start justify-between gap-2">
                  <div class="text-[13.5px] font-semibold">{{ $lang->nama_bahasa }}</div>
                  <div class="flex gap-2 text-[11.5px]">
                    <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'bahasa', $lang->id]) }}">Edit</a>
                    <form method="POST" action="{{ route('sub.destroy', [$employee, 'bahasa', $lang->id]) }}" onsubmit="return confirm('Hapus riwayat bahasa ini?')">
                      @csrf @method('DELETE')
                      <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                    </form>
                  </div>
                </div>
                @if($lang->tingkat) <div class="mt-1"><span class="badge" style="background:var(--blue-50); color:var(--blue-600); border-color:var(--blue-100)">{{ $lang->tingkat }}</span></div> @endif
                <div class="text-[11.5px] mt-1.5" style="color:var(--ink-500)">Kemampuan: {{ $lang->kemampuan ?? '-' }}</div>
                @if($lang->keterangan)<div class="text-[11.5px] mt-1" style="color:var(--ink-500)">{{ $lang->keterangan }}</div>@endif
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    {{-- ===== TAB: KEPEGAWAIAN ===== --}}
    <div class="tab-panel hidden" id="tab-kepegawaian">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
        <div class="p-5 rounded-xl" style="border:1px solid var(--line)">
          <h3 class="section-title mb-4">Kondisi Saat Ini</h3>
          @include('employees._kv', ['items' => [
            'Jabatan' => $employee->currentPosition?->name, 'Jenis Jabatan' => ucfirst($employee->jenis_jabatan ?? ''),
            'Eselon' => $employee->eselon ? $employee->eselon . (optional($employee->tmt_eselon)->format('d M Y') ? ' · TMT ' . $employee->tmt_eselon->format('d M Y') : '') : null,
            'TMT Jabatan' => optional($employee->tmt_jabatan)->format('d M Y'),
            'Unit Kerja' => $employee->workUnit?->name, 'TMT SKPD' => optional($employee->tmt_skpd)->format('d M Y'),
            'Golongan Awal' => $employee->golonganAwal?->golongan . ($employee->tmt_golongan_awal ? ' · TMT ' . $employee->tmt_golongan_awal->format('d M Y') : ''),
            'Golongan Akhir' => $employee->golonganAkhir?->golongan . ($employee->tmt_golongan_akhir ? ' · TMT ' . $employee->tmt_golongan_akhir->format('d M Y') : ''),
            'Masa Kerja' => $employee->masa_kerja_tahun !== null ? $employee->masa_kerja_tahun . ' th ' . ($employee->masa_kerja_bulan ?? 0) . ' bln' : null,
            'TMT Gaji Berkala' => optional($employee->tmt_gaji_berkala_terbaru)->format('d M Y'),
          ]])
        </div>
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-4">Ringkasan Status</h3>
          @include('employees._kv', ['items' => [
            'Status Pegawai' => $employee->status_pegawai, 'Jenis ASN' => $employee->jenis_asn,
            'Kategori' => $employee->employeeCategory?->name, 'Status Kerja' => $employee->employmentStatus?->name,
            'Tugas Tambahan' => $employee->tugas_tambahan_1 ? trim($employee->tugas_tambahan_1 . (optional($employee->tmt_tugas_tambahan_1)->format('d M Y') ? ' · TMT ' . $employee->tmt_tugas_tambahan_1->format('d M Y') : '')) : null,
            'Gaji Pokok' => $employee->gaji_pokok ? ($isSA ? 'Rp ' . number_format($employee->gaji_pokok, 0, ',', '.') : (auth()->user()->role === 'admin' ? 'Rp ' . number_format($employee->gaji_pokok, 0, ',', '.') : 'Rp ••••')) : null,
          ]])
        </div>
      </div>

      {{-- Riwayat Jabatan --}}
      @include('employees._section', [
        'title' => 'Riwayat Jabatan',
        'sub' => null,
        'createUrl' => route('sub.create', [$employee, 'jabatan']),
        'createLabel' => 'Tambah',
      ])
      @if($employee->positionHistories->isEmpty())
        <div class="empty-state"><p class="text-[13px]">Belum ada riwayat jabatan.</p></div>
      @else
        <div class="timeline">
          @foreach ($employee->positionHistories->sortByDesc('tmt') as $ph)
            <div class="tl-item">
              <span class="tl-dot"></span>
              <div class="tl-year">{{ optional($ph->tmt)->format('Y') }}</div>
              <div class="text-[14px] font-semibold mt-0.5">{{ $ph->position?->name ?? '' }}</div>
              <div class="text-[12px]" style="color:var(--ink-500)">
                TMT {{ optional($ph->tmt)->format('d M Y') ?? '' }}{{ $ph->workUnit?->name ? ' · ' . $ph->workUnit?->name : '' }}
                @if($ph->no_sk)<div class="mt-0.5">SK: {{ $ph->no_sk }}</div>@endif
              </div>
              <div class="flex gap-1.5 mt-2 text-[11.5px]">
                @if($ph->file_sk_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['jabatan', $ph->id]) }}">Unduh SK</a>@endif
                <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'jabatan', $ph->id]) }}">Edit</a>
                <form method="POST" action="{{ route('sub.destroy', [$employee, 'jabatan', $ph->id]) }}" onsubmit="return confirm('Hapus riwayat jabatan ini?')">
                  @csrf @method('DELETE')
                  <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                </form>
              </div>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Pangkat & Golongan --}}
      <div class="mt-10">
        @include('employees._section', [
          'title' => 'Pangkat & Golongan',
          'createUrl' => route('sub.create', [$employee, 'pangkat']),
          'createLabel' => 'Tambah',
        ])
        @if($employee->golonganAkhir)
          <div class="mb-5 p-4 rounded-xl" style="background:var(--blue-50); border:1px solid var(--blue-100)">
            <div class="text-[11px] font-semibold" style="color:var(--ink-500)">GOLONGAN SEKARANG</div>
            <div class="text-[20px] font-bold" style="color:var(--blue-600)">{{ $employee->golonganAkhir->golongan }}</div>
            <div class="text-[12px]" style="color:var(--ink-500)">{{ $employee->golonganAkhir->pangkat ?? '' }}@if($employee->tmt_golongan_akhir) · TMT {{ $employee->tmt_golongan_akhir->format('d M Y') }}@endif</div>
          </div>
        @endif
        @if($employee->rankHistories->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada riwayat pangkat.</p></div>
        @else
          <div class="timeline">
            @foreach ($employee->rankHistories->sortByDesc('tmt') as $rh)
              <div class="tl-item">
                <span class="tl-dot"></span>
                <div class="tl-year">{{ optional($rh->tmt)->format('Y') }}</div>
                <div class="text-[14px] font-semibold mt-0.5">{{ $rh->rank->golongan ?? '' }}</div>
                <div class="text-[12px]" style="color:var(--ink-500)">
                  {{ $rh->rank->pangkat ?? '' }} · TMT {{ optional($rh->tmt)->format('d M Y') ?? '' }}
                  @if($rh->no_sk)<div class="mt-0.5">SK: {{ $rh->no_sk }}</div>@endif
                </div>
                <div class="flex gap-1.5 mt-2 text-[11.5px]">
                  @if($rh->file_sk_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['pangkat', $rh->id]) }}">Unduh SK</a>@endif
                  <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'pangkat', $rh->id]) }}">Edit</a>
                  <form method="POST" action="{{ route('sub.destroy', [$employee, 'pangkat', $rh->id]) }}" onsubmit="return confirm('Hapus riwayat pangkat ini?')">
                    @csrf @method('DELETE')
                    <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Riwayat Mutasi --}}
      <div class="mt-10">
        @include('employees._section', [
          'title' => 'Riwayat Mutasi',
          'createUrl' => route('sub.create', [$employee, 'mutasi']),
          'createLabel' => 'Tambah',
        ])
        @if($employee->mutations->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada riwayat mutasi.</p></div>
        @else
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach ($employee->mutations->sortByDesc('tanggal_mutasi') as $m)
              <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                <div class="flex items-center justify-between gap-2">
                  <span class="badge" style="background:var(--blue-50); color:var(--blue-600); border-color:var(--blue-100)">{{ $m->jenis_mutasi }}</span>
                  <div class="text-[11px]" style="color:var(--ink-300)">{{ optional($m->tanggal_mutasi)->format('d M Y') }}</div>
                </div>
                <div class="text-[13.5px] font-semibold mt-2">
                  {{ $m->jabatan_lama ?: ($m->unitAsal?->name ?? '') }} → {{ $m->jabatan_baru ?: ($m->unitTujuan?->name ?? '') }}
                </div>
                @if($m->no_sk)<div class="text-[11.5px] mt-1" style="color:var(--ink-500)">SK: {{ $m->no_sk }}</div>@endif
                <div class="flex gap-2 mt-2 text-[11.5px]">
                  @if($m->file_sk_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['mutasi', $m->id]) }}">Unduh SK</a>@endif
                  <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'mutasi', $m->id]) }}">Edit</a>
                  <form method="POST" action="{{ route('sub.destroy', [$employee, 'mutasi', $m->id]) }}" onsubmit="return confirm('Hapus riwayat mutasi ini?')">
                    @csrf @method('DELETE')
                    <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- KGB (hanya admin) --}}
      @if($isAdmin)
        <div class="mt-10">
          @include('employees._section', [
            'title' => 'Riwayat KGB (Gaji Berkala)',
            'createUrl' => route('sub.create', [$employee, 'kgb']),
            'createLabel' => 'Tambah',
          ])
          @if($employee->salaryHistories->isEmpty())
            <div class="empty-state"><p class="text-[13px]">Belum ada riwayat KGB.</p></div>
          @else
            <div class="table-wrap">
              <table class="w-full min-w-[720px]">
                <thead><tr>
                  <th class="table-th">Golongan</th><th class="table-th">Gaji Pokok</th><th class="table-th">No. SK</th>
                  <th class="table-th">Tanggal SK</th><th class="table-th">TMT</th><th class="table-th">Masa Kerja</th><th class="table-th text-right">Aksi</th>
                </tr></thead>
                <tbody>
                  @foreach ($employee->salaryHistories->sortByDesc('tanggal_sk') as $s)
                    <tr class="row-line">
                      <td class="table-td font-medium">{{ $s->rank->golongan ?? '' }}</td>
                      <td class="table-td">Rp {{ number_format($s->gaji_pokok ?? 0, 0, ',', '.') }}</td>
                      <td class="table-td">{{ $s->no_sk ?? '' }}</td>
                      <td class="table-td">{{ optional($s->tanggal_sk)->format('d M Y') ?? '' }}</td>
                      <td class="table-td">{{ optional($s->tmt)->format('d M Y') ?? '' }}</td>
                      <td class="table-td">{{ trim(($s->masa_kerja_tahun ?? '' ? $s->masa_kerja_tahun . ' th' : '') . ' ' . ($s->masa_kerja_bulan ?? '' ? $s->masa_kerja_bulan . ' bln' : '')) }}</td>
                      <td class="table-td text-right">
                        <div class="flex justify-end gap-2 text-[11.5px]">
                          @if($s->file_sk_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['kgb', $s->id]) }}">Unduh</a>@endif
                          <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'kgb', $s->id]) }}">Edit</a>
                          <form method="POST" action="{{ route('sub.destroy', [$employee, 'kgb', $s->id]) }}" onsubmit="return confirm('Hapus riwayat KGB ini?')">
                            @csrf @method('DELETE')
                            <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      @endif

      {{-- PMK --}}
      <div class="mt-10">
        @include('employees._section', [
          'title' => 'Peninjauan Masa Kerja (PMK)',
          'createUrl' => route('sub.create', [$employee, 'pmk']),
          'createLabel' => 'Tambah',
        ])
        @if($employee->pmkHistories->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada riwayat PMK.</p></div>
        @else
          <div class="table-wrap">
            <table class="w-full min-w-[680px]">
              <thead><tr>
                <th class="table-th">No. SK</th><th class="table-th">Tanggal SK</th><th class="table-th">TMT</th>
                <th class="table-th">Tambahan</th><th class="table-th">Keterangan</th><th class="table-th text-right">Aksi</th>
              </tr></thead>
              <tbody>
                @foreach ($employee->pmkHistories->sortByDesc('tanggal_sk') as $pm)
                  <tr class="row-line">
                    <td class="table-td font-medium">{{ $pm->no_sk ?? '' }}</td>
                    <td class="table-td">{{ optional($pm->tanggal_sk)->format('d M Y') ?? '' }}</td>
                    <td class="table-td">{{ optional($pm->tmt)->format('d M Y') ?? '' }}</td>
                    <td class="table-td">{{ trim(($pm->tambah_tahun ? $pm->tambah_tahun . ' th' : '') . ' ' . ($pm->tambah_bulan ? $pm->tambah_bulan . ' bln' : '')) }}</td>
                    <td class="table-td">{{ $pm->keterangan ?? '' }}</td>
                    <td class="table-td text-right">
                      <div class="flex justify-end gap-2 text-[11.5px]">
                        @if($pm->file_sk_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['pmk', $pm->id]) }}">Unduh</a>@endif
                        <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'pmk', $pm->id]) }}">Edit</a>
                        <form method="POST" action="{{ route('sub.destroy', [$employee, 'pmk', $pm->id]) }}" onsubmit="return confirm('Hapus riwayat PMK ini?')">
                          @csrf @method('DELETE')
                          <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

      {{-- Riwayat Cuti --}}
      <div class="mt-10">
        @include('employees._section', [
          'title' => 'Riwayat Cuti',
          'createUrl' => route('sub.create', [$employee, 'cuti']),
          'createLabel' => 'Tambah',
        ])
        @if($employee->leaves->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada riwayat cuti.</p></div>
        @else
          <div class="table-wrap">
            <table class="w-full min-w-[680px]">
              <thead><tr>
                <th class="table-th">Jenis Cuti</th><th class="table-th">Tanggal Mulai</th><th class="table-th">Tanggal Selesai</th>
                <th class="table-th">Hari</th><th class="table-th">No. SK</th><th class="table-th text-right">Aksi</th>
              </tr></thead>
              <tbody>
                @foreach ($employee->leaves->sortByDesc('tanggal_mulai') as $lv)
                  <tr class="row-line">
                    <td class="table-td font-medium">{{ $lv->jenis_cuti }}</td>
                    <td class="table-td">{{ optional($lv->tanggal_mulai)->format('d M Y') ?? '' }}</td>
                    <td class="table-td">{{ optional($lv->tanggal_selesai)->format('d M Y') ?? '' }}</td>
                    <td class="table-td">{{ $lv->jumlah_hari ?? '' }}</td>
                    <td class="table-td">{{ $lv->no_sk ?? '' }}</td>
                    <td class="table-td text-right">
                      <div class="flex justify-end gap-2 text-[11.5px]">
                        @if($lv->file_sk_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['cuti', $lv->id]) }}">Unduh</a>@endif
                        <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'cuti', $lv->id]) }}">Edit</a>
                        <form method="POST" action="{{ route('sub.destroy', [$employee, 'cuti', $lv->id]) }}" onsubmit="return confirm('Hapus riwayat cuti ini?')">
                          @csrf @method('DELETE')
                          <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

      {{-- Riwayat Inaktif --}}
      <div class="mt-10">
        @include('employees._section', [
          'title' => 'Riwayat Inaktif',
          'createUrl' => route('sub.create', [$employee, 'inaktif']),
          'createLabel' => 'Tambah',
        ])
        @if($employee->inactivePeriods->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada riwayat inaktif.</p></div>
        @else
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach ($employee->inactivePeriods->sortByDesc('tanggal_mulai') as $ia)
              <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                <div class="flex items-center justify-between gap-2">
                  <span class="badge" style="background:var(--amber-50); color:var(--amber-600); border-color:var(--amber-100)">{{ $ia->status }}</span>
                  <div class="text-[11px]" style="color:var(--ink-300)">{{ optional($ia->tanggal_mulai)->format('d M Y') }} – {{ optional($ia->tanggal_selesai)->format('d M Y') }}</div>
                </div>
                @if($ia->alasan)<div class="text-[12px] mt-2" style="color:var(--ink-700)">{{ $ia->alasan }}</div>@endif
                @if($ia->no_sk)<div class="text-[11.5px] mt-1" style="color:var(--ink-500)">SK: {{ $ia->no_sk }}</div>@endif
                <div class="flex gap-2 mt-2 text-[11.5px]">
                  <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'inaktif', $ia->id]) }}">Edit</a>
                  <form method="POST" action="{{ route('sub.destroy', [$employee, 'inaktif', $ia->id]) }}" onsubmit="return confirm('Hapus riwayat inaktif ini?')">
                    @csrf @method('DELETE')
                    <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Riwayat Kontrak PPPK --}}
      <div class="mt-10">
        @include('employees._section', [
          'title' => 'Riwayat Kontrak PPPK',
          'createUrl' => route('sub.create', [$employee, 'kontrak_pppk']),
          'createLabel' => 'Tambah',
        ])
        @if($employee->pppkContracts->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada riwayat kontrak PPPK.</p></div>
        @else
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach ($employee->pppkContracts->sortByDesc('tanggal_mulai') as $pp)
              <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                <div class="flex items-center justify-between gap-2">
                  <div class="text-[13.5px] font-semibold">{{ $pp->nomor_kontrak }}</div>
                  <span class="badge" style="background:var(--teal-50); color:var(--teal-600); border-color:var(--teal-100)">{{ $pp->masa_kerja ?? 'Kontrak' }}</span>
                </div>
                <div class="text-[12px] mt-1.5" style="color:var(--ink-500)">
                  {{ optional($pp->tanggal_mulai)->format('d M Y') }} – {{ optional($pp->tanggal_selesai)->format('d M Y') }}
                  @if($pp->instansi)<div class="mt-0.5">Instansi: {{ $pp->instansi }}</div>@endif
                </div>
                <div class="flex gap-2 mt-2 text-[11.5px]">
                  @if($pp->file_kontrak_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['kontrak_pppk', $pp->id]) }}">Unduh Kontrak</a>@endif
                  <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'kontrak_pppk', $pp->id]) }}">Edit</a>
                  <form method="POST" action="{{ route('sub.destroy', [$employee, 'kontrak_pppk', $pp->id]) }}" onsubmit="return confirm('Hapus riwayat kontrak ini?')">
                    @csrf @method('DELETE')
                    <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Kontak Darurat --}}
      <div class="mt-10">
        @include('employees._section', [
          'title' => 'Kontak Darurat',
          'createUrl' => route('sub.create', [$employee, 'kontak_darurat']),
          'createLabel' => 'Tambah',
        ])
        @if($employee->emergencyContacts->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada kontak darurat.</p></div>
        @else
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($employee->emergencyContacts as $ec)
              <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                <div class="flex items-start justify-between gap-2">
                  <div>
                    <div class="text-[13.5px] font-semibold">{{ $ec->nama }}</div>
                    <div class="text-[12px]" style="color:var(--ink-500)">{{ $ec->hubungan ?? '' }}</div>
                  </div>
                  <div class="flex gap-2 text-[11.5px]">
                    <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'kontak_darurat', $ec->id]) }}">Edit</a>
                    <form method="POST" action="{{ route('sub.destroy', [$employee, 'kontak_darurat', $ec->id]) }}" onsubmit="return confirm('Hapus kontak darurat ini?')">
                      @csrf @method('DELETE')
                      <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                    </form>
                  </div>
                </div>
                @if($ec->telepon)<div class="mt-2 text-[12.5px] font-semibold" style="color:var(--blue-600)">{{ $ec->telepon }}</div>@endif
                @if($ec->alamat)<div class="text-[11.5px] mt-1" style="color:var(--ink-500)">{{ $ec->alamat }}</div>@endif
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Kedudukan Hukum --}}
      <div class="mt-10">
        @include('employees._section', [
          'title' => 'Kedudukan Hukum',
          'createUrl' => route('sub.create', [$employee, 'kedudukan_hukum']),
          'createLabel' => 'Tambah',
        ])
        @if($employee->legalStatuses->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada catatan kedudukan hukum.</p></div>
        @else
          <div class="table-wrap">
            <table class="w-full min-w-[680px]">
              <thead><tr>
                <th class="table-th">Status</th><th class="table-th">Kasus</th><th class="table-th">Tanggal</th>
                <th class="table-th">No. Putusan</th><th class="table-th text-right">Aksi</th>
              </tr></thead>
              <tbody>
                @foreach ($employee->legalStatuses as $ls)
                  <tr class="row-line">
                    <td class="table-td font-medium">{{ $ls->status }}</td>
                    <td class="table-td">{{ $ls->kasus ?? '' }}</td>
                    <td class="table-td">{{ optional($ls->tanggal)->format('d M Y') ?? '' }}</td>
                    <td class="table-td">{{ $ls->no_putusan ?? '' }}</td>
                    <td class="table-td text-right">
                      <div class="flex justify-end gap-2 text-[11.5px]">
                        @if($ls->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['kedudukan_hukum', $ls->id]) }}">Unduh</a>@endif
                        <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'kedudukan_hukum', $ls->id]) }}">Edit</a>
                        <form method="POST" action="{{ route('sub.destroy', [$employee, 'kedudukan_hukum', $ls->id]) }}" onsubmit="return confirm('Hapus catatan ini?')">
                          @csrf @method('DELETE')
                          <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

      {{-- Hukdis (hanya admin) --}}
      @if($isAdmin)
        <div class="mt-10">
          @include('employees._section', [
            'title' => 'Catatan Hukuman Disiplin',
            'sub' => 'Khusus Admin/Super Admin.',
            'createUrl' => route('sub.create', [$employee, 'hukdis']),
            'createLabel' => 'Tambah',
          ])
          @if($employee->disciplines->isEmpty())
            <div class="empty-state"><p class="text-[13px]">Tidak ada catatan pelanggaran / disiplin.</p></div>
          @else
            <div class="table-wrap">
              <table class="w-full min-w-[680px]">
                <thead><tr><th class="table-th">Jenis Pelanggaran</th><th class="table-th">Tanggal</th><th class="table-th">Tingkat</th><th class="table-th">Sanksi</th><th class="table-th">No. Keputusan</th><th class="table-th text-right">Aksi</th></tr></thead>
                <tbody>
                  @foreach ($employee->disciplines as $d)
                    <tr class="row-line">
                      <td class="table-td font-medium">{{ $d->jenis_pelanggaran }}</td>
                      <td class="table-td">{{ optional($d->tanggal)->format('d M Y') ?? '' }}</td>
                      <td class="table-td">{{ $d->tingkat_pelanggaran ?? '' }}</td>
                      <td class="table-td">{{ $d->sanksi ?? '' }}</td>
                      <td class="table-td">{{ $d->no_keputusan ?? '' }}</td>
                      <td class="table-td text-right">
                        <div class="flex justify-end gap-2 text-[11.5px]">
                          @if($d->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['hukdis', $d->id]) }}">Unduh</a>@endif
                          <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'hukdis', $d->id]) }}">Edit</a>
                          <form method="POST" action="{{ route('sub.destroy', [$employee, 'hukdis', $d->id]) }}" onsubmit="return confirm('Hapus catatan hukdis ini?')">
                            @csrf @method('DELETE')
                            <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      @endif

      {{-- Riwayat Penyakit (hanya admin) --}}
      @if($isAdmin)
        <div class="mt-10">
          @include('employees._section', [
            'title' => 'Riwayat Penyakit',
            'sub' => 'Khusus Admin/Super Admin.',
            'createUrl' => route('sub.create', [$employee, 'penyakit']),
            'createLabel' => 'Tambah',
          ])
          @if($employee->diseases->isEmpty())
            <div class="empty-state"><p class="text-[13px]">Belum ada riwayat penyakit.</p></div>
          @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              @foreach ($employee->diseases->sortByDesc('tanggal') as $dz)
                <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                  <div class="text-[13.5px] font-semibold">{{ $dz->nama_penyakit }}</div>
                  <div class="text-[11.5px] mt-0.5" style="color:var(--ink-500)">{{ optional($dz->tanggal)->format('d M Y') ?? '' }}</div>
                  @if($dz->keterangan)<div class="text-[11.5px] mt-2" style="color:var(--ink-700)">{{ $dz->keterangan }}</div>@endif
                  <div class="flex gap-2 mt-2 text-[11.5px]">
                    @if($dz->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['penyakit', $dz->id]) }}">Unduh</a>@endif
                    <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'penyakit', $dz->id]) }}">Edit</a>
                    <form method="POST" action="{{ route('sub.destroy', [$employee, 'penyakit', $dz->id]) }}" onsubmit="return confirm('Hapus riwayat penyakit ini?')">
                      @csrf @method('DELETE')
                      <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                    </form>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      @endif
    </div>

    {{-- ===== TAB: KINERJA & PENGHARGAAN ===== --}}
    <div class="tab-panel hidden" id="tab-kinerja-penghargaan">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <div class="flex items-center justify-between mb-4">
            <h3 class="section-title mb-0">Penilaian Kinerja</h3>
            <a href="{{ route('sub.create', [$employee, 'kinerja']) }}" class="btn-outline px-3 py-1.5 rounded-lg text-[12px] font-medium flex-none">+ Tambah</a>
          </div>
          @if($employee->performances->isEmpty())
            <div class="empty-state"><p class="text-[13px]">Belum ada penilaian kinerja.</p></div>
          @else
            <div class="grid grid-cols-1 gap-3">
              @foreach ($employee->performances->sortByDesc('tahun') as $p)
                <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                  <div class="flex items-center justify-between mb-2">
                    <div class="text-[13.5px] font-semibold">Kinerja {{ $p->tahun ?? '' }}</div>
                    <span class="badge" style="background:{{ ($p->nilai ?? 0) >= 75 ? 'var(--green-50)' : 'var(--amber-50)' }}; color:{{ ($p->nilai ?? 0) >= 75 ? 'var(--green-600)' : 'var(--amber-600)' }}; border-color:{{ ($p->nilai ?? 0) >= 75 ? 'var(--green-100)' : 'var(--amber-100)' }}">{{ $p->predikat ?? '' }}</span>
                  </div>
                  @if($p->nilai !== null)
                    <div class="flex items-center gap-2 mb-1">
                      <div class="progress flex-1"><div style="width:{{ min($p->nilai, 100) }}%; background:linear-gradient(90deg, var(--teal-500), var(--blue-600))"></div></div>
                      <span class="text-[13px] font-bold" style="color:var(--blue-600)">{{ number_format($p->nilai, 0) }}%</span>
                    </div>
                  @endif
                  @if($p->periode)<div class="text-[11.5px]" style="color:var(--ink-500)">Periode {{ $p->periode }}</div>@endif
                  @if($p->pejabat_penilai)<div class="text-[11.5px]" style="color:var(--ink-500)">Pejabat Penilai: {{ $p->pejabat_penilai }}</div>@endif
                  <div class="flex gap-2 mt-2 pt-2 text-[11.5px]" style="border-top:1px solid var(--line-soft)">
                    @if($p->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['kinerja', $p->id]) }}">Unduh</a>@endif
                    <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'kinerja', $p->id]) }}">Edit</a>
                    <form method="POST" action="{{ route('sub.destroy', [$employee, 'kinerja', $p->id]) }}" onsubmit="return confirm('Hapus penilaian ini?')">
                      @csrf @method('DELETE')
                      <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                    </form>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>

        <div>
          <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft); min-height:100%">
            <div class="flex items-center justify-between mb-4">
              <h3 class="section-title mb-0">SKP</h3>
              <a href="{{ route('sub.create', [$employee, 'skp']) }}" class="btn-outline px-3 py-1.5 rounded-lg text-[12px] font-medium flex-none">+ Tambah</a>
            </div>
            @if($employee->skps->isEmpty())
              <div class="empty-state"><p class="text-[13px]">Belum ada SKP.</p></div>
            @else
              <div class="grid grid-cols-1 gap-3">
                @foreach ($employee->skps->sortByDesc('tahun') as $sk)
                  <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                    <div class="flex items-center justify-between gap-2">
                      <div class="text-[13.5px] font-semibold">SKP {{ $sk->tahun ?? '' }}{{ $sk->periode ? ' · ' . $sk->periode : '' }}</div>
                      <span class="badge" style="background:var(--blue-50); color:var(--blue-600); border-color:var(--blue-100)">{{ $sk->predikat ?? '' }}</span>
                    </div>
                    @if($sk->uraian_kegiatan)<div class="text-[12px] mt-1.5" style="color:var(--ink-700)">{{ $sk->uraian_kegiatan }}</div>@endif
                    @if($sk->nilai !== null)<div class="text-[12px] mt-1" style="color:var(--ink-500)">Nilai: <strong style="color:var(--blue-600)">{{ number_format($sk->nilai, 2, ',', '.') }}</strong></div>@endif
                    <div class="flex gap-2 mt-2 pt-2 text-[11.5px]" style="border-top:1px solid var(--line-soft)">
                      @if($sk->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['skp', $sk->id]) }}">Unduh</a>@endif
                      <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'skp', $sk->id]) }}">Edit</a>
                      <form method="POST" action="{{ route('sub.destroy', [$employee, 'skp', $sk->id]) }}" onsubmit="return confirm('Hapus SKP ini?')">
                        @csrf @method('DELETE')
                        <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                      </form>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Angka Kredit --}}
      <div class="mt-8">
        @include('employees._section', [
          'title' => 'Angka Kredit',
          'createUrl' => route('sub.create', [$employee, 'angka_kredit']),
          'createLabel' => 'Tambah',
        ])
        @if($employee->creditScores->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada pencatatan angka kredit.</p></div>
        @else
          <div class="table-wrap">
            <table class="w-full min-w-[680px]">
              <thead><tr>
                <th class="table-th">Tahun</th><th class="table-th">Unsur</th><th class="table-th">Butir Kegiatan</th>
                <th class="table-th">Nilai AK</th><th class="table-th">Keterangan</th><th class="table-th text-right">Aksi</th>
              </tr></thead>
              <tbody>
                @foreach ($employee->creditScores->sortByDesc('tahun') as $cs)
                  <tr class="row-line">
                    <td class="table-td font-medium">{{ $cs->tahun ?? '' }}</td>
                    <td class="table-td">{{ $cs->unsur ?? '' }}</td>
                    <td class="table-td">{{ $cs->butir_kegiatan ?? '' }}</td>
                    <td class="table-td">{{ $cs->nilai_angka_kredit !== null ? number_format($cs->nilai_angka_kredit, 2, ',', '.') : '' }}</td>
                    <td class="table-td">{{ $cs->keterangan ?? '' }}</td>
                    <td class="table-td text-right">
                      <div class="flex justify-end gap-2 text-[11.5px]">
                        @if($cs->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['angka_kredit', $cs->id]) }}">Unduh</a>@endif
                        <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'angka_kredit', $cs->id]) }}">Edit</a>
                        <form method="POST" action="{{ route('sub.destroy', [$employee, 'angka_kredit', $cs->id]) }}" onsubmit="return confirm('Hapus angka kredit ini?')">
                          @csrf @method('DELETE')
                          <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

      {{-- IPASN --}}
      <div class="mt-10">
        @include('employees._section', [
          'title' => 'Indeks Profesionalitas ASN (IPASN)',
          'createUrl' => route('sub.create', [$employee, 'ipasn']),
          'createLabel' => 'Tambah',
        ])
        @if($employee->ipasns->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada catatan IPASN.</p></div>
        @else
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($employee->ipasns->sortByDesc('tahun') as $ip)
              <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                <div class="flex items-center justify-between gap-2">
                  <div class="text-[13.5px] font-semibold">IPASN {{ $ip->tahun ?? '' }}</div>
                  <span class="badge" style="background:var(--teal-50); color:var(--teal-600); border-color:var(--teal-100)">{{ $ip->predikat ?? '' }}</span>
                </div>
                <div class="text-[12px] mt-1.5" style="color:var(--ink-500)">{{ $ip->komponen ?? 'Total Indeks' }}</div>
                @if($ip->nilai !== null)<div class="text-[18px] font-bold mt-1" style="color:var(--blue-600)">{{ number_format($ip->nilai, 2, ',', '.') }}</div>@endif
                <div class="flex gap-2 mt-2 pt-2 text-[11.5px]" style="border-top:1px solid var(--line-soft)">
                  @if($ip->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['ipasn', $ip->id]) }}">Unduh</a>@endif
                  <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'ipasn', $ip->id]) }}">Edit</a>
                  <form method="POST" action="{{ route('sub.destroy', [$employee, 'ipasn', $ip->id]) }}" onsubmit="return confirm('Hapus catatan IPASN ini?')">
                    @csrf @method('DELETE')
                    <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Penghargaan --}}
      <div class="mt-10">
        @include('employees._section', [
          'title' => 'Penghargaan',
          'createUrl' => route('sub.create', [$employee, 'penghargaan']),
          'createLabel' => 'Tambah',
        ])
        @if($employee->awards->isEmpty())
          <div class="empty-state">
            <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 3a4 4 0 00-7 2.6L12 18l3-2.4L18 5.6A4 4 0 0016 3zM12 15l-3.5 6 2-4h3l2 4-3.5-6z"/></svg></div>
            <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Belum Ada Penghargaan</p>
            <p class="text-[12.5px] mt-1">Penghargaan & piagam yang diterima pegawai dicatat di sini.</p>
          </div>
        @else
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($employee->awards as $award)
              <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                <div class="flex items-center justify-between mb-2">
                  <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background:var(--amber-50); color:var(--amber-600)">🏅</div>
                  <div class="flex gap-2 text-[11.5px]">
                    @if($award->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['penghargaan', $award->id]) }}">Unduh</a>@endif
                    <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'penghargaan', $award->id]) }}">Edit</a>
                    <form method="POST" action="{{ route('sub.destroy', [$employee, 'penghargaan', $award->id]) }}" onsubmit="return confirm('Hapus penghargaan ini?')">
                      @csrf @method('DELETE')
                      <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                    </form>
                  </div>
                </div>
                <div class="text-[13.5px] font-semibold">{{ $award->nama_penghargaan }}</div>
                <div class="text-[12px]" style="color:var(--ink-500)">
                  Tahun {{ $award->tahun ?? '' }}{{ $award->pemberi_penghargaan ? ' · ' . $award->pemberi_penghargaan : '' }}
                </div>
                @if($award->no_penghargaan)<div class="text-[11.5px] mt-1" style="color:var(--ink-500)">Piagam: {{ $award->no_penghargaan }}</div>@endif
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    {{-- ===== TAB: KELUARGA ===== --}}
    <div class="tab-panel hidden" id="tab-keluarga">
      @include('employees._section', [
        'title' => 'Anggota Keluarga',
        'createUrl' => route('sub.create', [$employee, 'keluarga']),
        'createLabel' => 'Tambah Anggota',
      ])
      @if($employee->families->isEmpty())
        <div class="empty-state">
          <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
          <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Belum Ada Data Keluarga</p>
          <p class="text-[12.5px] mt-1">Pasangan, anak, orang tua, dan saudara dicatat di sini.</p>
        </div>
      @else
        @foreach (['pasangan' => 'Suami / Istri', 'anak' => 'Anak', 'orang_tua' => 'Orang Tua', 'saudara' => 'Saudara'] as $grp => $grpLabel)
          @php $members = $employee->families->where('type', $grp); @endphp
          @if($members->isNotEmpty())
            <h3 class="section-title mb-3 mt-6">{{ $grpLabel }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
              @foreach ($members as $f)
                <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                  <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                      <span class="w-9 h-9 rounded-full flex items-center justify-center" style="background:var(--teal-50); color:var(--teal-600)">👤</span>
                      <div>
                        <div class="text-[13.5px] font-semibold">{{ $f->nama }}</div>
                        <div class="text-[12px]" style="color:var(--ink-500)">{{ $f->status ?: ucfirst($f->type) }}</div>
                      </div>
                    </div>
                    <div class="flex gap-2 text-[11.5px]">
                      <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$employee, 'keluarga', $f->id]) }}">Edit</a>
                      <form method="POST" action="{{ route('sub.destroy', [$employee, 'keluarga', $f->id]) }}" onsubmit="return confirm('Hapus anggota keluarga ini?')">
                        @csrf @method('DELETE')
                        <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                      </form>
                    </div>
                  </div>
                  <div class="grid grid-cols-2 gap-x-4 mt-3 pt-3 text-[11.5px]" style="border-top:1px solid var(--line-soft)">
                    <div><span style="color:var(--ink-300)">NIK</span><br><span style="color:var(--ink-700)">{{ $f->nik ?? '' }}</span></div>
                    <div><span style="color:var(--ink-300)">Lahir</span><br><span style="color:var(--ink-700)">{{ trim(($f->tempat_lahir ?? '') . ', ' . ($f->tanggal_lahir?->format('d M Y') ?? '')) ?: '' }}</span></div>
                    <div><span style="color:var(--ink-300)">Pekerjaan</span><br><span style="color:var(--ink-700)">{{ $f->pekerjaan ?? '' }}</span></div>
                    <div><span style="color:var(--ink-300)">Tanggungan</span><br>
                      <span class="badge" style="background:{{ $f->status_tanggungan ? 'var(--green-50)' : 'var(--line-soft)' }}; color:{{ $f->status_tanggungan ? 'var(--green-600)' : 'var(--ink-500)' }}; border-color:{{ $f->status_tanggungan ? 'var(--green-100)' : 'var(--line)' }}">
                        {{ $f->status_tanggungan ? 'Tertanggung' : 'Tidak' }}
                      </span>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        @endforeach
      @endif
    </div>

    {{-- ===== TAB: ASET PEGAWAI ===== --}}
    @php $asetCanManage = $isAdmin || $employee->user_id === auth()->id(); @endphp
    @include('employees._panel-aset', ['e' => $employee, 'admin' => $asetCanManage])

    {{-- ===== TAB: RIWAYAT PERUBAHAN ===== --}}
    @php
      $historyActor = function ($l) use ($employee) {
        return $l->user_name ?? 'Sistem';
      };
      $historyLabel = function ($l) {
        $map = [
          'create' => ['Tambah', 'var(--green-600)', 'var(--green-50)', 'var(--green-100)'],
          'update' => ['Ubah', 'var(--blue-600)', 'var(--blue-50)', 'var(--blue-100)'],
          'delete' => ['Hapus', 'var(--red-600)', 'var(--red-50)', 'var(--red-100)'],
          'approve' => ['Disetujui SA', 'var(--green-600)', 'var(--green-50)', 'var(--green-100)'],
          'reject' => ['Ditolak', 'var(--red-600)', 'var(--red-50)', 'var(--red-100)'],
          'login' => ['Login', 'var(--ink-500)', 'var(--ink-50)', 'var(--line)'],
        ];
        $m = $map[$l->action] ?? [$l->action, 'var(--ink-600)', 'var(--ink-50)', 'var(--line)'];
        return $m;
      };
      $historyLogs = $employee->auditLogs ?? collect();
      $hasHistory = $historyLogs->isNotEmpty() || $employee->changeRequests->isNotEmpty();
    @endphp
    <div class="tab-panel hidden" id="tab-riwayat-perubahan">
      <div class="flex items-center justify-between mb-4">
        <h3 class="section-title">Riwayat Perubahan & Aktivitas</h3>
        @if($isSA)
          <a href="{{ route('approvals.index') }}" class="btn-outline px-3 py-1.5 rounded-lg text-[12px] font-medium">Buka Approval</a>
        @endif
      </div>
      @if(!$hasHistory)
        <div class="empty-state">
          <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
          <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Belum Ada Aktivitas</p>
          <p class="text-[12.5px] mt-1">Aktivitas data (pengajuan perubahan, penyimpanan, upload dokumen, approval) pegawai ini tercatat di sini.</p>
        </div>
      @else
        <div class="space-y-3">
          @foreach ($historyLogs as $l)
            @php [$hTxt, $hFg, $hBg, $hBd] = $historyLabel($l); @endphp
            <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="text-[13.5px] font-semibold">{{ $l->module }} · {{ $l->description }}</div>
                  <div class="text-[11.5px] mt-0.5" style="color:var(--ink-500)">
                    {{ $historyActor($l) }} · {{ $l->created_at ? $l->created_at->format('d M Y H:i') : '' }}
                  </div>
                </div>
                <span class="badge flex-none" style="background:{{ $hBg }}; color:{{ $hFg }}; border-color:{{ $hBd }}">{{ $hTxt }}</span>
              </div>
            </div>
          @endforeach
          @foreach ($employee->changeRequests->sortByDesc('created_at') as $cr)
            @php
              $pill = match ($cr->status) {
                'approved' => ['bg' => 'var(--green-50)', 'fg' => 'var(--green-600)', 'bd' => 'var(--green-100)', 'txt' => 'Disetujui'],
                'rejected' => ['bg' => 'var(--red-50)', 'fg' => 'var(--red-600)', 'bd' => 'var(--red-100)', 'txt' => 'Ditolak'],
                default => ['bg' => 'var(--amber-50)', 'fg' => 'var(--amber-600)', 'bd' => 'var(--amber-100)', 'txt' => 'Menunggu'],
              };
            @endphp
            <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="text-[13.5px] font-semibold">Pengajuan {{ str_replace('_', ' ', $cr->module_type) }} · {{ $cr->description }}</div>
                  <div class="text-[11.5px] mt-0.5" style="color:var(--ink-500)">
                    Diajukan {{ optional($cr->created_at)->format('d M Y H:i') }}
                    @if($cr->reviewed_at) · Ditinjau {{ optional($cr->reviewed_at)->format('d M Y H:i') }} @endif
                  </div>
                </div>
                <span class="badge flex-none" style="background:{{ $pill['bg'] }}; color:{{ $pill['fg'] }}; border-color:{{ $pill['bd'] }}">{{ $pill['txt'] }}</span>
              </div>
              @if($cr->data_new && is_array($cr->data_new))
                <div class="mt-3 pt-3 text-[11.5px] grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-1" style="border-top:1px solid var(--line-soft)">
                  @foreach ($cr->data_new as $field => $val)
                    @if(!empty($val) && $val !== $employee->{$field})
                      <div><span style="color:var(--ink-300)">{{ str_replace('_', ' ', ucwords($field)) }}</span><br><span style="color:var(--ink-700)">{{ $val }}</span></div>
                    @endif
                  @endforeach
                </div>
              @endif
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>

<script>
  const MORE_TABS = new Set(['pendidikan-diklat','kinerja-penghargaan','keluarga','aset','riwayat-perubahan']);
  function pickMore(el){
    setTab(el.dataset.tab);
    const dd = document.getElementById('dd-tabs-more');
    if(dd){ dd.classList.remove('open'); }
  }
  function setTab(name){
    name = String(name).replace(/^tab-/, '');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    const btn = document.querySelector('.tab-btn[data-tab="' + name + '"]');
    if(btn){ btn.classList.add('active'); }
    const panel = document.getElementById('tab-' + name);
    if(panel){ panel.classList.remove('hidden'); }
    document.querySelectorAll('#dd-tabs-more .menu-item').forEach(m => {
      const active = m.dataset.tab === name;
      m.classList.toggle('active', active);
    });
    const label = document.getElementById('tab-more-label');
    const icon = document.getElementById('tab-more-icon');
    if(label && icon){
      if(MORE_TABS.has(name)){
        const item = document.querySelector('#dd-tabs-more .menu-item[data-tab="' + name + '"]');
        const nm = item ? item.querySelector('.mi-name') : null;
        label.textContent = nm ? nm.textContent : 'Lainnya';
        icon.innerHTML = item ? item.querySelector('.mi-ic svg').outerHTML : icon.innerHTML;
      } else {
        label.textContent = 'Lainnya';
        icon.innerHTML = '<svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>';
      }
    }
  }
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      setTab(btn.dataset.tab);
    });
  });
  @if($frag = session('fragment'))
    setTab({{ json_encode($frag) }});
  @endif
</script>
@endsection