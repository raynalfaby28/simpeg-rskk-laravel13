@extends('layouts.app')
@section('title', 'Profil Saya')
@section('nav-profile', 'active')
@section('crumb', 'Profil Saya')

@php
  $isSA = auth()->user()->role === 'super_admin';
  $isAdmin = in_array(auth()->user()->role, ['super_admin', 'admin']);
  $roleLabel = ['super_admin' => 'Super Admin', 'admin' => 'Admin', 'user' => 'Pegawai'][auth()->user()->role] ?? '';
  $editableFields = ['nama_lengkap','gelar_depan','gelar_belakang','nik','tempat_lahir','tanggal_lahir','jenis_kelamin','agama','status_perkawinan','alamat_rumah','kelurahan_rumah','kecamatan_rumah','kabkota_rumah','provinsi_rumah','kodepos_rumah','hp','email_pribadi','employment_status_id','employee_category_id','jenis_asn','work_unit_id','current_position_id','jenis_jabatan','golongan_akhir_id','tmt_jabatan','tmt_skpd','masa_kerja_tahun','masa_kerja_bulan'];
  $emp = $employee;
  if ($emp) {
    $fullName = trim(implode(' ', array_filter([$emp->gelar_depan, $emp->nama_lengkap, $emp->gelar_belakang])));
    $nameSrc = $fullName ?: auth()->user()->name;
    $words = preg_split('/\s+/', trim($nameSrc)) ?: [];
    $initials = mb_strtoupper(collect($words)->filter(fn ($w) => $w !== '')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('')) ?: '?';
    $filledCount = collect($editableFields)->filter(fn ($f) => filled($emp->{$f}))->count();
    $completeness = (int) round($filledCount / count($editableFields) * 100);
    $genderLabel = $emp->jenis_kelamin === 'P' ? 'Perempuan' : ($emp->jenis_kelamin === 'L' ? 'Laki-laki' : null);
    $unitName = $emp->workUnit?->name;
    $jabatan = $emp->currentPosition?->name;
    $statusPegawai = $emp->employmentStatus?->name ?: $emp->status_pegawai;
    $golonganLabel = $emp->golonganAkhir
      ? $emp->golonganAkhir->golongan . ($emp->golonganAkhir->pangkat ? ' (' . $emp->golonganAkhir->pangkat . ')' : '')
      : null;
    $masaKerja = ($emp->masa_kerja_tahun || $emp->masa_kerja_bulan)
      ? trim(($emp->masa_kerja_tahun ? $emp->masa_kerja_tahun . ' tahun' : '') . ' ' . ($emp->masa_kerja_bulan ? $emp->masa_kerja_bulan . ' bulan' : ''))
      : null;
    $kv = fn ($v) => filled($v) ? e($v) : '<span class="ik-dim">Belum diisi</span>';
    $hint = $completeness >= 100 ? 'Data Anda sudah lengkap.'
      : ($completeness >= 50 ? 'Data pribadi dan kepegawaian Anda sudah cukup lengkap.' : 'Lengkapi data Anda agar profil semakin lengkap.');
    $profileHeroUrl = ($pp = \App\Models\Settings::get('login_photo_path')) && \Illuminate\Support\Facades\Storage::disk('public')->exists($pp)
      ? \Illuminate\Support\Facades\Storage::url($pp)
      : asset('assets/images/login-maskot.png');
  }
@endphp

@section('content')
<style>
  /* ===== Profil Saya — Hero enterprise Gedung RSKK (Gelombang 10) ===== */
  .prof-actions{ display:flex; align-items:center; flex-wrap:wrap; gap:12px; }
  .hero-card{ border-radius:22px; overflow:hidden; border-color:var(--line-soft); box-shadow:var(--shadow-md); }
  .hero-media{ position:relative; height:300px; overflow:hidden;
               background:linear-gradient(115deg, var(--navy-800) 0%, var(--blue-700) 55%, var(--blue-600) 100%); }
  .hero-media .hero-img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; object-position:center 30%;
                         -webkit-mask-image:linear-gradient(180deg,#000 48%, rgba(0,0,0,.3) 75%, transparent 100%);
                         mask-image:linear-gradient(180deg,#000 48%, rgba(0,0,0,.3) 75%, transparent 100%); }
  .hero-shade{ position:absolute; inset:0; pointer-events:none;
               background:linear-gradient(180deg, rgba(9,14,32,.36) 0%, rgba(9,14,32,.16) 45%, rgba(9,14,32,0) 74%); }
  .hero-top{ position:absolute; left:0; right:0; top:0; padding:28px 40px 0; display:flex; align-items:center;
             justify-content:space-between; gap:12px; pointer-events:none; }
  .hero-pill{ display:inline-flex; align-items:center; gap:8px; padding:7px 15px; border-radius:999px;
              font-size:10.5px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#fff;
              background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.30);
              backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); box-shadow:0 4px 14px rgba(9,14,32,.22); }
  .hero-pill svg{ width:13px; height:13px; }
  .hero-nip{ font-size:11px; font-weight:500; letter-spacing:.04em;
             font-family:ui-monospace, SFMono-Regular, Menlo, monospace; color:rgba(255,255,255,.78);
             background:rgba(9,14,32,.24); padding:5px 12px; border-radius:999px; border:1px solid rgba(255,255,255,.16); }
  .hero-content{ position:relative; z-index:2; display:flex; align-items:flex-end; gap:28px; padding:0 40px 34px; margin-top:-74px; }
  .hero-state{ flex:1 1 auto; min-width:0; }
  .hero-name{ font-family:var(--font-head); font-size:26px; font-weight:700; letter-spacing:-.015em;
              color:var(--navy-900); line-height:1.2; }
  .hero-badges{ display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
  .hero-badges .badge{ transition:transform .18s ease, border-color .18s ease, box-shadow .18s ease; }
  .hero-badges .badge:hover{ transform:scale(1.02); }
  .hero-line{ display:flex; align-items:center; gap:8px; font-size:14px; font-weight:600; color:var(--ink-800); margin-top:9px; }
  .hero-line svg{ width:15px; height:15px; flex:none; color:var(--blue-600); }
  .hero-line.sub{ font-size:13px; font-weight:500; color:var(--ink-500); }
  .hero-line.sub svg{ width:14px; height:14px; color:var(--ink-300); }
  .hero-comp{ max-width:520px; margin-top:22px; }
  .hero-comp-top{ display:flex; align-items:center; justify-content:space-between; gap:12px;
                  font-size:11.5px; margin-bottom:7px; }
  .hero-comp-top .lbl{ color:var(--ink-500); font-weight:600; }
  .hero-comp-top .val{ font-weight:700; color:var(--navy-900); font-variant-numeric:tabular-nums; }
  .hero-progress{ height:11px; border-radius:999px; background:var(--line-soft); overflow:hidden; position:relative; }
  .hero-progress .hpg{ height:100%; border-radius:999px; width:var(--val);
                       background:linear-gradient(90deg, var(--blue-600), #38BDF8); }
  .hero-progress .hpg::after{ content:''; position:absolute; inset:0; border-radius:999px;
    background:linear-gradient(90deg, transparent, rgba(255,255,255,.5), transparent);
    animation:barSweep 2.6s ease-in-out infinite; }
  .hero-hint{ font-size:11.5px; color:var(--ink-400); margin-top:7px; }
  .hero-summary{ flex:0 0 352px; min-width:0; background:#fff; border:1px solid var(--line-soft);
                 border-radius:20px; padding:22px 24px; box-shadow:var(--shadow-md); }
  .sum-head{ display:flex; align-items:center; gap:10px; padding-bottom:14px; margin-bottom:4px;
             border-bottom:1px solid var(--line-soft); }
  .sum-head .ic{ width:34px; height:34px; border-radius:10px; display:flex; align-items:center;
                 justify-content:center; flex:none; background:linear-gradient(135deg,var(--blue-600),var(--blue-500));
                 color:#fff; box-shadow:0 5px 12px -5px rgba(37,99,235,.5); }
  .sum-head .ic svg{ width:17px; height:17px; }
  .sum-head .tt{ font-family:var(--font-head); font-size:14px; font-weight:700; color:var(--ink-900); }
  .sum-head .st{ font-size:11px; color:var(--ink-300); margin-top:1px; }
  .sum-row{ display:flex; align-items:flex-start; gap:12px; padding:13px 2px; }
  .sum-row .ic{ width:30px; height:30px; flex:none; display:flex; align-items:center; justify-content:center;
                border-radius:9px; background:var(--blue-50); color:var(--blue-600); margin-top:1px; }
  .sum-row .ic svg{ width:14px; height:14px; }
  .sum-row .tx{ min-width:0; }
  .sum-row .lbl{ font-size:10.5px; color:var(--ink-300); letter-spacing:.03em; }
  .sum-row .val{ font-size:13px; font-weight:700; color:var(--ink-900); line-height:1.45; margin-top:1px; word-break:break-word; }
  .sum-sep{ height:1px; background:var(--line-soft); margin:0 2px; }
  .pc-avatar{ width:144px; height:144px; border-radius:50%; overflow:hidden; flex:none; position:relative;
              background:var(--blue-600); border:4px solid #fff; box-shadow:0 12px 28px rgba(16,24,40,.22); }
  .pc-avatar img{ width:100%; height:100%; object-fit:cover; display:block; }
  .pc-avatar .pa-inits{ position:absolute; inset:0; display:none; align-items:center; justify-content:center;
    color:#fff; font-family:var(--font-head); font-weight:700; font-size:46px; }

  @keyframes heroIn{ from{ opacity:0; transform:translateY(10px); } to{ opacity:1; transform:translateY(0); } }
  @keyframes heroLiftIn{ from{ opacity:0; transform:translateY(14px); } to{ opacity:1; transform:translateY(0); } }
  @keyframes heroFromRight{ from{ opacity:0; transform:translateX(14px); } to{ opacity:1; transform:translateX(0); } }
  @keyframes hpgrow{ from{ width:0%; } }
  @media (prefers-reduced-motion:no-preference){
    .hero-card{ animation:heroIn .6s cubic-bezier(.22,1,.36,1) backwards; }
    .hero-state{ animation:heroLiftIn .5s cubic-bezier(.22,1,.36,1) .12s backwards; }
    .hero-summary{ animation:heroFromRight .55s cubic-bezier(.22,1,.36,1) .22s backwards; }
    .hero-progress .hpg{ animation:hpgrow 1.05s cubic-bezier(.22,1,.36,1) .4s backwards; }
    .profile-avatar-wrap .pc-avatar, .profile-avatar-wrap .avatar-edit, .profile-avatar-wrap .avatar-delete{ transition:transform .2s cubic-bezier(.22,1,.36,1), box-shadow .2s ease; }
    .profile-avatar-wrap:hover .pc-avatar{ transform:scale(1.02); }
  }
  @media (prefers-reduced-motion:reduce){
    .hero-card, .hero-state, .hero-summary, .hero-progress .hpg{ animation:none !important; }
    .hero-progress .hpg{ width:var(--val) !important; }
  }
  @media (min-width:721px) and (max-width:1024px){
    .hero-media{ height:250px; }
    .hero-content{ flex-wrap:wrap; }
    .hero-summary{ flex:1 1 100%; }
  }
  @media (max-width:720px){
    .hero-media{ height:200px; }
    .hero-top{ padding:18px 20px 0; }
    .hero-pill{ padding:6px 12px; font-size:10px; }
    .hero-nip{ display:none; }
    .hero-content{ margin-top:-46px; padding:0 20px 24px; gap:18px; }
    .hero-name{ font-size:20px; }
    .pc-avatar{ width:98px; height:98px; border-width:3px; }
    .pc-avatar .pa-inits{ font-size:32px; }
    .hero-summary{ flex:1 1 100%; border-radius:16px; padding:18px; }
    .hero-comp{ max-width:100%; }
  }
  /* Tabs — refined */
  .tab-btn{ display:inline-flex; align-items:center; gap:7px; padding:13px 14px 12px; }
  .tab-btn svg{ width:15px; height:15px; flex:none; opacity:.75; }
  /* Tombol "Lainnya" — dropdown di ujung tab bar */
  .tab-more{ display:inline-flex; align-items:center; gap:7px; padding:7px 12px; margin:8px 2px 8px 6px;
             border-radius:10px; font-size:13px; font-weight:600; color:var(--ink-700); background:#fff;
             border:1px solid var(--line); transition:.15s; white-space:nowrap; cursor:pointer; }
  .tab-more:hover{ border-color:var(--blue-600); color:var(--blue-600); background:var(--blue-50); }
  .tab-more .chev{ color:var(--ink-300); margin-left:1px; transition:color .15s; }
  .tab-more:hover .chev{ color:var(--blue-600); }
  .dropdown.open .tab-more{ border-color:var(--blue-600); color:var(--blue-600); background:var(--blue-50); }
  /* Panel dropdown "Lainnya" — elegan */
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
  /* Ringkasan stat cards */
  .rk-card{ padding:16px 18px; border:1px solid var(--line-soft); border-radius:14px; background:linear-gradient(180deg,#fff,#FAFBFD); box-shadow:var(--shadow-sm); }
  .rk-card .rk-ic{ width:38px; height:38px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex:none; }
  .rk-card .rk-label{ font-size:11px; font-weight:600; color:var(--ink-300); letter-spacing:.04em; text-transform:uppercase; margin-top:10px; }
  .rk-card .rk-value{ font-size:14px; font-weight:700; color:var(--ink-900); margin-top:2px; line-height:1.4; }
  .rk-card .rk-sub{ font-size:11.5px; color:var(--ink-400); margin-top:1px; }
  .rk-panel-head{ display:flex; align-items:center; gap:10px; padding-bottom:12px; margin-bottom:14px; border-bottom:1px solid var(--line-soft); }
  .rk-panel-head .ic{ width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex:none; background:var(--blue-50); color:var(--blue-600); }
  .rk-panel-head .ic svg{ width:16px; height:16px; }
  .rk-panel-head .tt{ font-family:var(--font-head); font-size:14px; font-weight:700; color:var(--ink-900); }
  .rk-panel-head .st{ font-size:11.5px; color:var(--ink-300); margin-top:0px; }
</style>
<div class="profile-wrap">
  <div class="page-head no-print mb-6">
    <div>
      <h1 class="page-title">Profil Saya</h1>
      <p class="page-desc">Kelola informasi pribadi dan ajukan perubahan data untuk persetujuan Super Admin.</p>
    </div>
    <div class="prof-actions">
      <button onclick="window.print()" class="btn btn-outline">
        <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2z"/></svg>
        Print
      </button>
      <a href="{{ route('dashboard') }}" class="btn btn-outline">
        <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
      </a>
      <a href="#ajukan-perubahan" onclick="event.preventDefault(); setTab('ajukan'); return false;" class="btn btn-primary">
        <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9a4.2 4.2 0 10-5.9 5.9L19 13l6-6-3.6-3.6z"/></svg>
        Ajukan Perubahan
      </a>
    </div>
  </div>

@if(!$emp)
  <div class="card card-pad text-center" style="padding:48px 24px">
    <div class="es-ic">
      <svg style="width:26px;height:26px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    </div>
    <h3 style="font-size:15px;font-weight:700;margin:0 0 4px;color:var(--ink-900)">Akun belum terhubung ke data pegawai</h3>
    <p style="font-size:13px;margin:0 0 20px;color:var(--ink-500)">Akun ini tidak memiliki data pegawai. Silakan hubungi Super Admin agar akun dihubungkan ke data pegawai.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary">Kembali ke Dashboard</a>
  </div>
@else
  {{-- ===== Profil header — Hero Gedung RSKK ===== --}}
  <div class="card hero-card mb-6 no-print">
    <div class="hero-media">
      <img class="hero-img" src="{{ $profileHeroUrl }}" alt="Ilustrasi Rumah Sakit Kabupaten Klungkung">
      <div class="hero-shade" aria-hidden="true"></div>
      <div class="hero-top">
        <span class="hero-pill">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          Profil Saya
        </span>
        <span class="hero-nip">ID PEGAWAI · {{ $emp->nip ?? '—' }}</span>
      </div>
    </div>
    <div class="hero-content">
      <div class="profile-avatar-wrap">
        <div class="pc-avatar {{ $emp->foto_path ? 'image-present' : '' }}">
          @if($emp->foto_path)
            <img src="{{ asset('storage/' . $emp->foto_path) }}" class="avatar-zoom" onclick="openPhotoViewer('{{ asset('storage/' . $emp->foto_path) }}')" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" alt="{{ $fullName ?: auth()->user()->name }}" title="Perbesar foto">
          @else
            <img src="{{ asset('images/default-avatar.png') }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" alt="{{ $fullName ?: auth()->user()->name }}">
          @endif
          <span class="pa-inits" style="display:{{ $emp->foto_path ? 'none' : 'flex' }}">{{ $initials }}</span>
        </div>
        <button type="button" class="avatar-edit no-print" onclick="document.getElementById('foto-profil-input').click()" title="Ganti Foto" aria-label="Ganti foto profil">
          <svg style="width:13px;height:13px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.9a2 2 0 001.7-1l.6-1.2A2 2 0 017.9 4h8.2a2 2 0 011.7 1l.6 1.2a2 2 0 001.7 1H19a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </button>
        @if($emp->foto_path)
          <button type="button" class="avatar-delete no-print" onclick="event.preventDefault();document.getElementById('foto-profil-hapus').submit()" title="Hapus Foto" aria-label="Hapus foto profil">
            <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.9 12.1A2 2 0 0116.1 21H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
          </button>
          <form id="foto-profil-hapus" method="POST" action="{{ route('profile.photo-delete') }}" class="hidden no-print">
            @csrf
            @method('DELETE')
          </form>
        @endif
      </div>
      <div class="hero-state">
        <h1 class="hero-name">{{ $fullName ?: auth()->user()->name }}</h1>
        <div class="hero-badges">
          <span class="badge" style="background:var(--green-50);color:var(--green-600);border-color:var(--green-100)">
            <span class="dot" style="background:var(--green-500)"></span>Aktif
          </span>
          <span class="badge" style="background:var(--blue-50);color:var(--blue-600);border-color:var(--blue-100)">{{ $roleLabel }}</span>
        </div>
        <div class="hero-line">{{ $jabatan ?? '—' }}</div>
        <div class="hero-line sub">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5"/></svg>
          {{ $unitName ?? '—' }}
        </div>
        <div class="hero-comp no-print">
          <div class="hero-comp-top">
            <span class="lbl">Kelengkapan Data Profil</span>
            <span class="val">{{ $completeness }}%</span>
          </div>
          <div class="hero-progress" role="progressbar" aria-label="Kelengkapan data profil" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $completeness }}">
            <div class="hpg" style="--val:{{ $completeness }}%"></div>
          </div>
          <div class="hero-hint">{{ $hint }}</div>
        </div>
      </div>
      <aside class="hero-summary no-print" aria-label="Ringkasan identitas pegawai">
        <div class="sum-head">
          <span class="ic">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 3v5c0 4.6-3.2 8.7-8 10-4.8-1.3-8-5.4-8-10V6l8-3zm-3 9l2 2 4-4"/></svg>
          </span>
          <div>
            <div class="tt">Ringkasan Identitas</div>
            <div class="st">Data kepegawaian utama</div>
          </div>
        </div>
        <div class="sum-row"><span class="ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 11h3m-3 3h5"/></svg></span><div class="tx"><div class="lbl">NIP</div><div class="val">{{ $emp->nip ?? '—' }}</div></div></div>
        <div class="sum-sep"></div>
        <div class="sum-row"><span class="ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5"/></svg></span><div class="tx"><div class="lbl">Unit Kerja</div><div class="val">{{ $unitName ?? '—' }}</div></div></div>
        <div class="sum-sep"></div>
        <div class="sum-row"><span class="ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.3A9 9 0 0110.7 3 7 7 0 0021 13.3z"/></svg></span><div class="tx"><div class="lbl">Pangkat / Golongan</div><div class="val">{{ $golonganLabel ?? '—' }}</div></div></div>
        <div class="sum-sep"></div>
        <div class="sum-row" style="padding-bottom:2px"><span class="ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></span><div class="tx"><div class="lbl">Masa Kerja</div><div class="val">{{ $masaKerja ?? '—' }}</div></div></div>
      </aside>
    </div>
  </div>

  {{-- Form upload foto tersembunyi --}}
  <form id="foto-profil-form" method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" class="hidden no-print">
    @csrf
    <input type="file" id="foto-profil-input" name="foto" accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden" onchange="this.form.submit()">
  </form>

  {{-- ===== Pending change ===== --}}
  @if($pendingRequest)
    <div class="card cards-gap card-pad-sm pend-alert mb-6" style="border-color:#FCD34D;background:#FFFBF0">
      <span class="ic" style="background:#FEF3C7;color:#B45309">
        <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </span>
      <div class="txt">
        <p class="pend-t">Pengajuan perubahan sedang diproses</p>
        <p class="pend-s">Perubahan data menjadi resmi setelah disetujui Super Admin. Diajukan {{ $pendingRequest->created_at->diffForHumans() }}.</p>
      </div>
      @if(auth()->user()->role !== 'user')
        <a href="{{ route('approvals.show', $pendingRequest) }}" class="btn btn-outline btn-sm" style="flex:none">Lihat Detail</a>
      @endif
    </div>
  @endif

  {{-- ===== Tabs profiler ===== --}}
  <div class="card mb-6">
<div class="flex items-center px-2 border-b" style="border-color:var(--line)">
  <div class="flex items-center overflow-x-auto">
    {{-- Ringkasan --}}
    <button data-tab="ringkasan" class="tab-btn active">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 21h16V7l-7-3-9 3v14zm4-8h2m-2 4h2m6-8h2m-2 4h2m-4-2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
      Ringkasan
    </button>
    {{-- Data Pribadi --}}
    <button data-tab="pribadi" class="tab-btn">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
      Data Pribadi
    </button>
    {{-- Alamat & Kontak --}}
    <button data-tab="alamat-kontak" class="tab-btn">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.7 17.7A10.3 10.3 0 1121 12a10.3 10.3 0 01-3.3 5.7zM12 13a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg>
      Alamat & Kontak
    </button>
    {{-- Kepegawaian --}}
    <button data-tab="kepegawaian" class="tab-btn">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.3A9 9 0 0110.7 3 7 7 0 0021 13.3z"/></svg>
      Kepegawaian
    </button>
    </div>
    <div style="flex:1"></div>
    {{-- Lainnya (dropdown) --}}
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
            <div class="more-sub">Semua bagian data Anda</div>
          </div>
        </div>
        <div class="more-scroll">
        {{-- Arsip & Dokumen --}}
        <button type="button" class="menu-item more-item" data-tab="dokumen" onclick="pickMore(this)">
          <span class="mi-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2zM15 3v6h6"/></svg></span>
          <span class="mi-tx"><span class="mi-name">Arsip &amp; Dokumen</span><span class="mi-sub">Sertifikat, SK, dan lampiran</span></span>
          <svg class="m-chek" style="width:15px;height:15px;margin-left:auto;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </button>
        {{-- Pendidikan & Diklat --}}
        <button type="button" class="menu-item more-item" data-tab="pendidikan-diklat" onclick="pickMore(this)">
          <span class="mi-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.7 14.9L3 18.5V6.5l6.7-3.6L16.3 6.5v6M9.7 3.9V14m0 0l6.6-3.5M16.3 11.2l4.7-2.5V6.5m-4.7 9.2l4.7-2.5"/></svg></span>
          <span class="mi-tx"><span class="mi-name">Pendidikan &amp; Diklat</span><span class="mi-sub">Ijazah, pelatihan, dan sertifikasi</span></span>
          <svg class="m-chek" style="width:15px;height:15px;margin-left:auto;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </button>
        {{-- Kinerja & Penghargaan --}}
        <button type="button" class="menu-item more-item" data-tab="kinerja-penghargaan" onclick="pickMore(this)">
          <span class="mi-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.5 3.6L13.9 8l4.9.7-3.5 3.4.8 4.8-4.4-2.3-4.4 2.3.8-4.8-3.5-3.4 4.9-.7 2.4-4.4z"/></svg></span>
          <span class="mi-tx"><span class="mi-name">Kinerja &amp; Penghargaan</span><span class="mi-sub">SKP, penilaian, dan prestasi</span></span>
          <svg class="m-chek" style="width:15px;height:15px;margin-left:auto;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </button>
        {{-- Keluarga --}}
        <button type="button" class="menu-item more-item" data-tab="keluarga" onclick="pickMore(this)">
          <span class="mi-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.9M9 20H4v-2a4 4 0 013-3.9m6-1.6a4 4 0 10-4-4m8 0a4 4 0 11-4-4"/></svg></span>
          <span class="mi-tx"><span class="mi-name">Keluarga</span><span class="mi-sub">Pasangan, anak, dan orang tua</span></span>
          <svg class="m-chek" style="width:15px;height:15px;margin-left:auto;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </button>
        {{-- Aset --}}
        <button type="button" class="menu-item more-item" data-tab="aset" onclick="pickMore(this)">
          <span class="mi-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7M4 7l3-3m-3 3h16M17 4l3 3M8 21h8"/></svg></span>
          <span class="mi-tx"><span class="mi-name">Aset Pegawai</span><span class="mi-sub">Barang milik yang Anda kuasai</span></span>
          <svg class="m-chek" style="width:15px;height:15px;margin-left:auto;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </button>
        <div class="more-div"></div>
        {{-- Riwayat Perubahan --}}
        <button type="button" class="menu-item more-item" data-tab="riwayat-perubahan" onclick="pickMore(this)">
          <span class="mi-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
          <span class="mi-tx"><span class="mi-name">Riwayat Perubahan</span><span class="mi-sub">Log pengajuan dan persetujuan</span></span>
          <svg class="m-chek" style="width:15px;height:15px;margin-left:auto;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </button>
        {{-- Ajukan Perubahan --}}
        <button type="button" class="menu-item more-item" data-tab="ajukan" onclick="pickMore(this)">
          <span class="mi-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9a4.2 4.2 0 10-5.9 5.9L19 13l6-6-3.6-3.6z"/></svg></span>
          <span class="mi-tx"><span class="mi-name">Ajukan Perubahan</span><span class="mi-sub">Usulkan perbaikan data kepada SA</span></span>
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
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="rk-card">
          <span class="rk-ic" style="background:var(--blue-50);color:var(--blue-600)">
            <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
          </span>
          <div class="rk-label">Status Pegawai</div>
          <div class="rk-value">{{ $statusPegawai ?: '—' }}</div>
          @if($emp->status_pegawai && $emp->employmentStatus && $emp->status_pegawai !== $emp->employmentStatus->name)
            <div class="rk-sub">{{ $emp->status_pegawai }}</div>
          @endif
        </div>
        <div class="rk-card">
          <span class="rk-ic" style="background:var(--navy-50,#EEF1F6);color:var(--navy-700)">
            <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-13 8h14a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </span>
          <div class="rk-label">Masa Kerja</div>
          <div class="rk-value">{{ $masaKerja ?: '—' }}</div>
          <div class="rk-sub">Total masa kerja pegawai</div>
        </div>
        <div class="rk-card">
          <span class="rk-ic" style="background:var(--teal-50);color:var(--teal-600)">
            <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5"/></svg>
          </span>
          <div class="rk-label">Unit Kerja</div>
          <div class="rk-value" style="line-height:1.35">{{ $unitName ?: '—' }}</div>
        </div>
        <div class="rk-card">
          <span class="rk-ic" style="background:var(--amber-50);color:var(--amber-600)">
            <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.5 3.6L13.9 8l4.9.7-3.5 3.4.8 4.8-4.4-2.3-4.4 2.3.8-4.8-3.5-3.4 4.9-.7 2.4-4.4z"/></svg>
          </span>
          <div class="rk-label">Jabatan</div>
          <div class="rk-value" style="line-height:1.35">{{ $jabatan ?: '—' }}</div>
          @if($emp->jenis_jabatan)
            <div class="rk-sub">{{ ucfirst($emp->jenis_jabatan) }}</div>
          @endif
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <div class="rk-panel-head">
            <span class="ic">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </span>
            <div>
              <div class="tt">Informasi Pribadi</div>
              <div class="st">Data dasar identitas pegawai</div>
            </div>
          </div>
          @include('employees._kv', ['items' => [
            'Nama Lengkap' => $fullName,
            'NIP' => $emp->nip,
            'NIK' => $emp->nik,
            'Tempat, Tanggal Lahir' => ($emp->tempat_lahir || $emp->tanggal_lahir) ? trim(($emp->tempat_lahir ?? '') . ', ' . ($emp->tanggal_lahir?->format('d M Y') ?? '')) : null,
            'Jenis Kelamin' => $genderLabel,
            'Agama' => $emp->agama,
            'Status Perkawinan' => $emp->status_perkawinan,
          ]])
        </div>
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <div class="rk-panel-head">
            <span class="ic">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.3A9 9 0 0110.7 3 7 7 0 0021 13.3z"/></svg>
            </span>
            <div>
              <div class="tt">Informasi Kepegawaian</div>
              <div class="st">Data penempatan & pangkat</div>
            </div>
          </div>
          @include('employees._kv', ['items' => [
            'Kategori Pegawai' => $emp->employeeCategory?->name,
            'Jenis ASN' => $emp->jenis_asn,
            'Unit Kerja' => $unitName,
            'Jenis Jabatan' => $emp->jenis_jabatan ? ucfirst($emp->jenis_jabatan) : null,
            'Pangkat/Golongan' => $golonganLabel,
            'TMT Jabatan' => $emp->tmt_jabatan?->format('d M Y'),
            'TMT Pangkat' => $emp->tmt_golongan_akhir?->format('d M Y'),
            'Masa Kerja' => $masaKerja,
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
            'NIP' => $emp->nip, 'NIP Lama' => $emp->nip_lama, 'Nama Lengkap' => $emp->nama_lengkap,
            'Gelar Depan' => $emp->gelar_depan, 'Gelar Belakang' => $emp->gelar_belakang, 'Nama Panggilan' => $emp->nama_panggilan,
            'Tempat, Tanggal Lahir' => ($emp->tempat_lahir || $emp->tanggal_lahir) ? trim(($emp->tempat_lahir ?? '') . ', ' . ($emp->tanggal_lahir?->format('d M Y') ?? '')) : null,
            'Jenis Kelamin' => $genderLabel,
            'Agama' => $emp->agama, 'Golongan Darah' => $emp->golongan_darah,
          ]])
        </div>
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-2">Kependudukan & Nomor</h3>
          @include('employees._kv', ['items' => [
            'NIK / KTP' => $emp->nik, 'No. KK' => $emp->no_kk, 'NPWP' => $emp->no_npwp,
            'No. BPJS' => $emp->no_bpjs, 'KARIP / KARSU' => $emp->no_karis_karsu, 'KARPEG' => $emp->no_karpeg,
            'No. Taspen' => $emp->no_taspen, 'No. Rekening' => $emp->no_rekening ? ($emp->bank . ' ' . $emp->no_rekening) : null,
            'BA Pertarum' => $emp->bapertarum, 'Status Perkawinan' => $emp->status_perkawinan,
          ]])
        </div>
      </div>
      @if(!$emp->tempat_lahir)
        <p class="text-[12.5px] mt-5 px-4 py-3 rounded-lg alert alert-warning">
          Profil ini masih kosong — data baru terisi kalau dilengkapi lewat form Ajukan Perubahan.
        </p>
      @endif
    </div>

    {{-- ===== TAB: ALAMAT & KONTAK ===== --}}
    <div class="tab-panel hidden" id="tab-alamat-kontak">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-2">Alamat Rumah</h3>
          @include('employees._kv', ['items' => [
            'Alamat' => $emp->alamat_rumah,
            'RT / RW' => trim(($emp->rt_rumah ?? '') . ' / ' . ($emp->rw_rumah ?? '')),
            'Kelurahan' => $emp->kelurahan_rumah, 'Kecamatan' => $emp->kecamatan_rumah,
            'Kab/Kota' => $emp->kabkota_rumah, 'Provinsi' => $emp->provinsi_rumah,
            'Kode Pos' => $emp->kodepos_rumah,
          ]])
        </div>
        <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-2">Alamat Domisili (KTP)</h3>
          @include('employees._kv', ['items' => [
            'Alamat KTP' => optional($emp)->alamat_domisili_ktp,
            'RT / RW' => trim(optional($emp)->rt_domisili . ' / ' . optional($emp)->rw_domisili),
            'Kelurahan' => optional($emp)->kelurahan_domisili, 'Kecamatan' => optional($emp)->kecamatan_domisili,
            'Kab/Kota' => optional($emp)->kabkota_domisili, 'Provinsi' => optional($emp)->provinsi_domisili,
            'Kode Pos' => optional($emp)->kodepos_domisili,
          ]])
        </div>
        <div class="p-5 rounded-xl md:col-span-2" style="border:1px solid var(--line-soft)">
          <h3 class="section-title mb-2">Kontak</h3>
          @include('employees._kv', ['items' => [
            'No. Telepon' => optional($emp)->telp, 'No. HP' => optional($emp)->hp,
            'Email Pribadi' => optional($emp)->email_pribadi, 'Email Resmi' => optional($emp)->email_resmi,
          ]])
        </div>
      </div>
    </div>

    {{-- ===== TAB: ARSIP & DOKUMEN ===== --}}
    @include('employees._panel-dokumen', ['e' => $emp, 'admin' => $isAdmin])

    {{-- ===== TAB: PENDIDIKAN & DIKLAT ===== --}}
    @include('employees._panel-pendidikan-diklat', ['e' => $emp, 'admin' => true])

    {{-- ===== TAB: KEPEGAWAIAN ===== --}}
    @include('employees._panel-kepegawaian', ['e' => $emp, 'admin' => true, 'sensitive' => true])

    {{-- ===== TAB: KINERJA & PENGHARGAAN ===== --}}
    @include('employees._panel-kinerja-penghargaan', ['e' => $emp, 'admin' => true])

    {{-- ===== TAB: KELUARGA ===== --}}
    @include('employees._panel-keluarga', ['e' => $emp, 'admin' => true])

    {{-- ===== TAB: ASET ===== --}}
    @include('employees._panel-aset', ['e' => $emp, 'admin' => true])

    {{-- ===== TAB: RIWAYAT PERUBAHAN ===== --}}
    <div class="tab-panel hidden" id="tab-riwayat-perubahan">
      <div class="flex items-center justify-between mb-4">
        <h3 class="section-title">Riwayat Perubahan & Aktivitas</h3>
      </div>
      @php
        $changeLogs = $emp->changeRequests()->latest()->get();
        $auditLogs = $emp->auditLogs()->latest('created_at')->get();
      @endphp
      @if($changeLogs->count() || $auditLogs->count())
        <div class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Waktu</th><th>Status</th><th>Ringkasan Perubahan</th>
              </tr>
            </thead>
            <tbody>
              @foreach($auditLogs as $log)
                @php
                  $actionStyle = [
                    'create' => ['Tambah', '#047857', '#D1FAE5'],
                    'update' => ['Ubah', '#1D4ED8', '#DBEAFE'],
                    'delete' => ['Hapus', '#B91C1C', '#FEE2E2'],
                    'approve' => ['Disetujui', '#047857', '#D1FAE5'],
                    'reject' => ['Ditolak', '#B91C1C', '#FEE2E2'],
                  ];
                  [$stLabel, $stFg, $stBg] = $actionStyle[$log->action] ?? [$log->action, '#111827', '#F3F4F6'];
                @endphp
                <tr>
                  <td class="text-[12.5px]">{{ $log->created_at?->format('d M Y, H:i') }}</td>
                  <td>
                    <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;color:{{ $stFg }};background:{{ $stBg }}">{{ $stLabel }}</span>
                  </td>
                  <td class="text-[12.5px]" style="color:var(--ink-500)">{{ $log->module }} · {{ $log->description }}</td>
                </tr>
              @endforeach
              @foreach($changeLogs as $log)
                @php
                  $changed = collect($log->old_data ?? [])
                    ->keys()
                    ->filter(fn ($f) => ($log->new_data[$f] ?? null) != ($log->old_data[$f] ?? null))
                    ->take(4)
                    ->map(fn ($f) => ucwords(str_replace('_', ' ', preg_replace('/_id$/', '', $f))))
                    ->implode(', ');
                  $statusMap = ['pending' => ['Menunggu', '#B45309', '#FEF3C7'], 'approved' => ['Disetujui', '#047857', '#D1FAE5'], 'rejected' => ['Ditolak', '#B91C1C', '#FEE2E2']];
                  [$stLabel, $stFg, $stBg] = $statusMap[$log->status] ?? [$log->status, '#111827', '#F3F4F6'];
                @endphp
                <tr>
                  <td class="text-[12.5px]">{{ $log->created_at->format('d M Y, H:i') }}</td>
                  <td>
                    <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;color:{{ $stFg }};background:{{ $stBg }}">{{ $stLabel }}</span>
                  </td>
                  <td class="text-[12.5px]" style="color:var(--ink-500)">{{ $changed ?: ($log->new_data ? 'Perubahan data profil' : '—') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="empty-state">
          <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
          <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Belum Ada Aktivitas</p>
          <p class="text-[12.5px] mt-1">Riwayat perubahan dan aktivitas data Anda tercatat di sini.</p>
        </div>
      @endif
    </div>

    {{-- ===== TAB: AJUKAN PERUBAHAN ===== --}}
    <div class="tab-panel hidden" id="tab-ajukan">
      <form id="ajukan-perubahan" method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')
        <div class="flex items-start justify-between gap-3 flex-wrap mb-5">
          <div>
            <h3 class="section-title">Ajukan Perubahan Data</h3>
            <p class="text-[12.5px] mt-1" style="color:var(--ink-500)">Isi usulan perbaikan di bawah. Perubahan baru berlaku setelah disetujui Super Admin.</p>
          </div>
          <span style="padding:5px 12px;font-size:10.5px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;border-radius:999px;background:var(--blue-50);color:var(--blue-600);flex:none">Menunggu Persetujuan</span>
        </div>

        <h4 class="field-group">Informasi Pribadi</h4>
        <div class="fg">
          <div>
            <label class="flabel">Nama Lengkap <span class="req">*</span></label>
            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $emp->nama_lengkap) }}" required class="input">
          </div>
          <div>
            <label class="flabel">NIK</label>
            <input type="text" name="nik" value="{{ old('nik', $emp->nik) }}" class="input">
          </div>
          <div>
            <label class="flabel">Gelar Depan</label>
            <input type="text" name="gelar_depan" value="{{ old('gelar_depan', $emp->gelar_depan) }}" placeholder="Cth. dr." class="input">
          </div>
          <div>
            <label class="flabel">Gelar Belakang</label>
            <input type="text" name="gelar_belakang" value="{{ old('gelar_belakang', $emp->gelar_belakang) }}" placeholder="Cth. Sp.PD" class="input">
          </div>
          <div>
            <label class="flabel">Tempat Lahir</label>
            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $emp->tempat_lahir) }}" class="input">
          </div>
          <div>
            <label class="flabel">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($emp->tanggal_lahir)->format('Y-m-d')) }}" class="input">
          </div>
          <div>
            <label class="flabel">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="input">
              <option value="">Pilih</option>
              <option value="L" @selected(old('jenis_kelamin', $emp->jenis_kelamin) === 'L')>Laki-laki</option>
              <option value="P" @selected(old('jenis_kelamin', $emp->jenis_kelamin) === 'P')>Perempuan</option>
            </select>
          </div>
          <div>
            <label class="flabel">Agama</label>
            <select name="agama" class="input">
              <option value="">Pilih</option>
              @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Khonghucu','Lainnya'] as $a)
                <option value="{{ $a }}" @selected(old('agama', $emp->agama) === $a)>{{ $a }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="flabel">Status Perkawinan</label>
            <select name="status_perkawinan" class="input">
              <option value="">Pilih</option>
              @foreach(['Belum Kawin','Kawin','Cerai Hidup','Cerai Mati'] as $s)
                <option value="{{ $s }}" @selected(old('status_perkawinan', $emp->status_perkawinan) === $s)>{{ $s }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <h4 class="field-group">Alamat & Kontak</h4>
        <div class="fg">
          <div class="lg-2">
            <label class="flabel">Alamat Rumah</label>
            <input type="text" name="alamat_rumah" value="{{ old('alamat_rumah', $emp->alamat_rumah) }}" class="input">
          </div>
          <div>
            <label class="flabel">Kelurahan</label>
            <input type="text" name="kelurahan_rumah" value="{{ old('kelurahan_rumah', $emp->kelurahan_rumah) }}" class="input">
          </div>
          <div>
            <label class="flabel">Kecamatan</label>
            <input type="text" name="kecamatan_rumah" value="{{ old('kecamatan_rumah', $emp->kecamatan_rumah) }}" class="input">
          </div>
          <div>
            <label class="flabel">Kab/Kota</label>
            <input type="text" name="kabkota_rumah" value="{{ old('kabkota_rumah', $emp->kabkota_rumah) }}" class="input">
          </div>
          <div>
            <label class="flabel">Provinsi</label>
            <input type="text" name="provinsi_rumah" value="{{ old('provinsi_rumah', $emp->provinsi_rumah) }}" class="input">
          </div>
          <div>
            <label class="flabel">Kodepos</label>
            <input type="text" name="kodepos_rumah" value="{{ old('kodepos_rumah', $emp->kodepos_rumah) }}" class="input">
          </div>
          <div>
            <label class="flabel">No. HP</label>
            <input type="text" name="hp" value="{{ old('hp', $emp->hp) }}" class="input">
          </div>
          <div>
            <label class="flabel">Email Pribadi</label>
            <input type="email" name="email_pribadi" value="{{ old('email_pribadi', $emp->email_pribadi) }}" class="input">
          </div>
        </div>

        <h4 class="field-group">Informasi Kepegawaian</h4>
        <div class="fg">
          <div>
            <label class="flabel">Status Kerja</label>
            <select name="employment_status_id" class="input">
              <option value="">Pilih</option>
              @foreach($employmentStatuses as $es)
                <option value="{{ $es->id }}" @selected(old('employment_status_id', $emp->employment_status_id) == $es->id)>{{ $es->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="flabel">Kategori Pegawai</label>
            <select name="employee_category_id" class="input">
              <option value="">Pilih</option>
              @foreach($employeeCategories as $cat)
                <option value="{{ $cat->id }}" @selected(old('employee_category_id', $emp->employee_category_id) == $cat->id)>{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="flabel">Jenis ASN</label>
            <select name="jenis_asn" class="input">
              <option value="">Pilih</option>
              @foreach(['PNS', 'PPPK'] as $ja)
                <option value="{{ $ja }}" @selected(old('jenis_asn', $emp->jenis_asn) === $ja)>{{ $ja }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="flabel">Unit Kerja</label>
            <select name="work_unit_id" class="input">
              <option value="">Pilih</option>
              @foreach($workUnits as $wu)
                <option value="{{ $wu->id }}" @selected(old('work_unit_id', $emp->work_unit_id) == $wu->id)>{{ $wu->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="flabel">Jabatan</label>
            <select name="current_position_id" class="input">
              <option value="">Pilih</option>
              @foreach($positions as $pos)
                <option value="{{ $pos->id }}" @selected(old('current_position_id', $emp->current_position_id) == $pos->id)>{{ $pos->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="flabel">Jenis Jabatan</label>
            <select name="jenis_jabatan" class="input">
              <option value="">Pilih</option>
              @foreach(['struktural', 'fungsional', 'pelaksana'] as $jj)
                <option value="{{ $jj }}" @selected(old('jenis_jabatan', $emp->jenis_jabatan) === $jj)>{{ ucfirst($jj) }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="flabel">Pangkat / Golongan</label>
            <select name="golongan_akhir_id" class="input">
              <option value="">Pilih</option>
              @foreach($ranks as $rank)
                <option value="{{ $rank->id }}" @selected(old('golongan_akhir_id', $emp->golongan_akhir_id) == $rank->id)>{{ $rank->golongan }} {{ $rank->pangkat ? '— ' . $rank->pangkat : '' }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="flabel">TMT Jabatan</label>
            <input type="date" name="tmt_jabatan" value="{{ old('tmt_jabatan', optional($emp->tmt_jabatan)->format('Y-m-d')) }}" class="input">
          </div>
          <div>
            <label class="flabel">TMT SKPD / Unit Kerja</label>
            <input type="date" name="tmt_skpd" value="{{ old('tmt_skpd', optional($emp->tmt_skpd)->format('Y-m-d')) }}" class="input">
          </div>
          <div>
            <label class="flabel">Masa Kerja (Tahun)</label>
            <input type="number" min="0" max="70" name="masa_kerja_tahun" value="{{ old('masa_kerja_tahun', $emp->masa_kerja_tahun) }}" class="input">
          </div>
          <div>
            <label class="flabel">Masa Kerja (Bulan)</label>
            <input type="number" min="0" max="11" name="masa_kerja_bulan" value="{{ old('masa_kerja_bulan', $emp->masa_kerja_bulan) }}" class="input">
          </div>
        </div>

        <div class="card-pad" style="background:var(--blue-50);border:1px solid var(--blue-100);border-radius:12px;margin-bottom:20px;display:flex;gap:10px;align-items:flex-start">
          <svg style="width:17px;height:17px;flex:none;margin-top:1px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" color="#1D4ED8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div class="text-[12.5px]" style="color:var(--blue-800)">Perubahan akan ditinjau Super Admin. Data resmi hanya diperbarui setelah pengajuan disetujui.</div>
        </div>

        <div class="btn-row">
          <button type="submit" class="btn btn-primary">
            <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Ajukan Perubahan
          </button>
          <a href="{{ route('dashboard') }}" class="btn btn-ghost">Batal</a>
        </div>
      </form>
    </div>

  </div>
  </div>
@endif
</div>

<script>
  const MORE_TABS = new Set(['dokumen','pendidikan-diklat','kinerja-penghargaan','keluarga','aset','riwayat-perubahan','ajukan']);
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
  @if(session('fragment'))
    document.addEventListener('DOMContentLoaded', () => {
      const f = {{ Illuminate\Support\Js::from(session('fragment')) }};
      setTab(String(f).replace(/^tab-/, ''));
    });
  @endif
  if(window.location.hash === '#ajukan-perubahan'){ setTab('ajukan'); }
</script>
@endsection