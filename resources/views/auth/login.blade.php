<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk · SIMPEG RSKK</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; }
  body {
    font-family: 'Inter', system-ui, sans-serif; color: #0F172A;
    -webkit-font-smoothing: antialiased; overflow-x: hidden;
    background:
      radial-gradient(55% 45% at 6% 4%, rgba(37,99,235,.07), transparent 62%),
      radial-gradient(45% 40% at 96% 96%, rgba(56,189,248,.08), transparent 60%),
      linear-gradient(180deg, #F8FAFC, #F1F5F9);
    animation: bgIn .9s ease both;
  }
  @keyframes bgIn { from { opacity: 0; } to { opacity: 1; } }
  a { text-decoration: none; }

  .login-page { min-height: 100vh; display: flex; flex-direction: column; }

  /* Brand mini (muncul hanya di tampilan satu kolom / mobile): LOGO → VISUAL → CARD */
  .mob-brand { display: none; text-align: center; padding: 4px 20px 0; }
  .mob-brand img { width: 46px; height: auto; object-fit: contain; display: block; margin: 0 auto 8px; }
  .mob-brand .mb-name { font-size: 20px; font-weight: 800; letter-spacing: -.02em; color: #0F172A; }
  .mob-brand .mb-sub { font-size: 12px; font-weight: 500; color: #64748B; margin-top: 2px; }

  /* ===== Area utama: grid landscape, visual kiri jauh lebih besar ===== */
  .login-main {
    flex: 1; width: 100%; max-width: 1560px; margin: 0 auto;
    display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(360px, 0.9fr);
    align-items: center; gap: clamp(56px, 5vw, 84px);
    padding: clamp(28px, 4.5vh, 48px) clamp(24px, 5vw, 72px) 14px;
  }

  /* ---- Panel kiri: hero visual RSKK landscape ---- */
  .illust {
    position: relative; min-width: 0;
    min-height: clamp(500px, 60vh, 640px);
    display: flex; align-items: center; justify-content: center;
    border-radius: 32px; overflow: hidden;
    background:
      radial-gradient(120% 130% at 100% 0%, #EFF6FF 0%, transparent 55%),
      linear-gradient(128deg, #F5F9FF 0%, #EAF2FF 48%, #DCECFF 100%);
    border: 1px solid rgba(148,163,184,.14);
    animation: heroIn 1s cubic-bezier(.22,1,.36,1) both;
  }
  @keyframes heroIn { from { opacity: 0; transform: scale(.97); } to { opacity: 1; transform: scale(1); } }

  .illust .deco { position: absolute; border-radius: 50%; pointer-events: none; }
  .illust .deco.d1 { width: 380px; height: 380px; background: rgba(255,255,255,.5); top: -120px; left: -100px; animation: drift 7s ease-in-out infinite; }
  .illust .deco.d2 { width: 300px; height: 300px; background: rgba(255,255,255,.42); bottom: -110px; right: -80px; animation: drift 8s ease-in-out 1.1s infinite; }
  .illust .deco.d3 { width: 250px; height: 250px; border: 2px dashed rgba(59,130,246,.22); top: 16%; right: 5%; animation: ringSpin 40s linear infinite; }
  .illust .deco.d4 { width: 150px; height: 150px; background: rgba(125,211,252,.28); bottom: 10%; left: 6%; animation: haloPulse 6s ease-in-out .8s infinite; }
  @keyframes ringSpin { to { transform: rotate(360deg); } }
  @keyframes drift { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(0, -8px); } }

  /* Ambient glow + parallax halus (desktop) */
  .illust .ambient {
    position: absolute; inset: -12%; pointer-events: none;
    background: radial-gradient(38% 42% at 72% 28%, rgba(59,130,246,.16), transparent 70%);
    filter: blur(12px);
    transform: translate3d(var(--px, 0), var(--py, 0), 0);
    transition: transform .45s cubic-bezier(.22,1,.36,1);
    animation: glowShift 6.5s ease-in-out infinite;
  }
  @keyframes glowShift { 0%, 100% { opacity: .4; } 50% { opacity: .55; } }

  /* Grain halus seperti blueprint */
  .illust .grain { position: absolute; inset: 0; pointer-events: none; opacity: .45;
    background-image: radial-gradient(rgba(59,130,246,.16) 1px, transparent 1.6px); background-size: 22px 22px; }

  .illust-wrap { position: relative; z-index: 1; width: 100%; max-width: 680px; display: flex; justify-content: center; padding: 26px; }

  .illust-wrap::before {
    content: ""; position: absolute; bottom: 3%; width: 80%; aspect-ratio: 1; left: 50%; transform: translateX(-50%);
    background: radial-gradient(circle, rgba(255,255,255,.92), rgba(147,197,253,.4) 55%, transparent 72%);
    border-radius: 50%; filter: blur(4px);
    animation: glowPulse 6s ease-in-out infinite;
  }
  @keyframes glowPulse {
    0%, 100% { opacity: .45; transform: translateX(-50%) scale(1); }
    50% { opacity: .6; transform: translateX(-50%) scale(1.06); }
  }

  .illust-wrap img {
    position: relative; z-index: 2; width: auto; max-width: 92%; max-height: clamp(360px, 48vh, 560px); object-fit: contain;
    filter: drop-shadow(0 32px 40px rgba(15,52,120,.30));
    animation: photoFloat 5.5s ease-in-out 1s infinite;
  }
  @keyframes photoFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-7px); } }

  .illust-wrap .ph-empty {
    display: none; flex-direction: column; align-items: center; justify-content: center; gap: 10px;
    width: min(78%, 520px); aspect-ratio: 16 / 10; border: 2px dashed rgba(59,130,246,.28); border-radius: 24px;
    background: rgba(255,255,255,.55); color: #64748B; font-size: 13px; font-weight: 600;
  }

  /* Chip informasi mengambang di panel visual */
  .ill-chip {
    position: absolute; z-index: 3; display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; border-radius: 14px; pointer-events: none;
    background: rgba(255,255,255,.86); border: 1px solid rgba(255,255,255,.95);
    box-shadow: 0 16px 32px -14px rgba(37,99,235,.30), 0 0 0 1px rgba(226,232,240,.8);
    backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
  }
  .ill-chip .ic { width: 32px; height: 32px; border-radius: 9px; flex: none; display: flex; align-items: center; justify-content: center; }
  .ill-chip .ic svg { width: 16px; height: 16px; }
  .ill-chip .tt { font-size: 12.5px; font-weight: 700; color: #0F172A; }
  .ill-chip .st { font-size: 10.5px; color: #64748B; margin-top: 1px; }
  .chip-a { top: 30px; left: 30px; animation: chipFloat 6s ease-in-out infinite; }
  .chip-b { bottom: 32px; left: 38px; animation: chipFloat 7s ease-in-out 1.3s infinite; }
  .chip-c { top: 50%; right: 24px; animation: chipFloatC 5.6s ease-in-out .6s infinite; }
  @keyframes chipFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
  @keyframes chipFloatC { 0%, 100% { transform: translateY(-50%); } 50% { transform: translateY(calc(-50% - 8px)); } }

  /* ---- Kolom kanan: card login compact ---- */
  .login-side { position: relative; display: flex; align-items: center; justify-content: center; min-width: 0; animation: cardIn .8s cubic-bezier(.22,1,.36,1) both; }
  @keyframes cardIn { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }

  /* Glow lembut di belakang card (bukan garis, hanya cahaya) biar ada kedalaman */
  .login-side::before {
    content: ""; position: absolute; width: 380px; height: 380px; border-radius: 50%;
    background: radial-gradient(circle, rgba(37,99,235,.13), transparent 62%);
    filter: blur(10px); pointer-events: none; z-index: 0;
    top: 50%; left: 50%; margin: -190px 0 0 -190px;
    animation: blobPulse 7s ease-in-out infinite;
  }
  @keyframes blobPulse { 0%, 100% { opacity: .55; transform: scale(1); } 50% { opacity: .95; transform: scale(1.09); } }

  .login-card {
    position: relative; z-index: 1;
    width: 100%; max-width: 410px; background: rgba(255,255,255,.96);
    border-radius: 22px; box-shadow: 0 26px 64px -30px rgba(15,23,42,.32), 0 0 0 1px rgba(15,23,42,.05);
    padding: 36px 34px;
    transition: transform .3s ease, box-shadow .3s ease;
  }
  .login-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 34px 74px -30px rgba(15,23,42,.38), 0 0 0 1px rgba(15,23,42,.06);
  }

  /* Stagger entrance untuk elemen di dalam card */
  .stg { animation: riseIn .6s cubic-bezier(.22,1,.36,1) both; animation-delay: var(--d, 0s); }
  @keyframes riseIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

  .card-head { text-align: center; margin-bottom: 22px; }
  .ch-logo { height: 54px; width: auto; object-fit: contain; margin: 0 auto 12px; display: block; }
  .ch-fb {
    display: none; width: 54px; height: 54px; border-radius: 15px; margin: 0 auto 12px;
    align-items: center; justify-content: center; color: #fff;
    background: linear-gradient(135deg, #2563EB, #38BDF8);
  }
  .card-head h1 {
    font-size: 23px; font-weight: 800; letter-spacing: -.02em; color: #0F172A;
    background: linear-gradient(92deg, #1D4ED8 0%, #2563EB 45%, #0EA5E9 70%, #1D4ED8 100%);
    background-size: 200% auto; -webkit-background-clip: text; background-clip: text;
    -webkit-text-fill-color: transparent; animation: textShine 8s linear infinite;
  }
  @keyframes textShine { to { background-position: 200% center; } }
  .card-head .ch-sub { font-size: 13px; font-weight: 500; color: #64748B; margin-top: 3px; line-height: 1.5; }

  .card-head .ch-welcome { margin-top: 20px; }
  .card-head .ch-welcome h2 { font-size: 22px; font-weight: 700; color: #0F172A; }
  .card-head .ch-welcome p { font-size: 13px; color: #64748B; margin-top: 4px; line-height: 1.55; }

  .alert-error {
    display: flex; align-items: flex-start; gap: 9px; margin-bottom: 18px; padding: 12px 14px;
    border-radius: 12px; background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; font-size: 13px; line-height: 1.5;
    animation: errIn .3s ease both;
  }
  @keyframes errIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
  .alert-error svg { flex: none; margin-top: 2px; }

  .field { margin-bottom: 16px; }
  .field .flabel { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 7px; }
  .input-group { display: flex; height: 48px; }
  .input-icon {
    display: inline-flex; justify-content: center; align-items: center; width: 48px; flex: none;
    background: #F8FAFC; border: 1.5px solid #E2E8F0; border-right: 0; border-radius: 10px 0 0 10px; color: #94A3B8;
    transition: color .25s, background .25s, border-color .25s;
  }
  .input-group:focus-within .input-icon { color: #2563EB; background: #EFF6FF; border-color: #2563EB; }
  .login-input {
    flex: 1; min-width: 0; background: #fff; border: 1.5px solid #E2E8F0; border-left: 0;
    border-radius: 0 10px 10px 0; font-size: 14px; padding: 0 13px; color: #0F172A; font-family: inherit;
    transition: border-color .25s, box-shadow .25s;
  }
  .login-input::placeholder { color: #94A3B8; }
  .login-input:focus { outline: none; border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
  .login-input.pad-r { padding-right: 44px; }

  .pwd-wrap { position: relative; flex: 1; display: flex; min-width: 0; }
  .pwd-wrap .login-input { flex: 1; }
  .eye-btn {
    position: absolute; right: 4px; top: 0; height: 100%; width: 38px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: #94A3B8; border: none; background: transparent; transition: color .2s, transform .15s;
  }
  .eye-btn:hover { color: #2563EB; }
  .eye-btn:active { transform: scale(.9); }

  .field .err { display: none; color: #EF4444; font-size: 12px; margin-top: 5px; }
  .field .err.show { display: block; animation: fadeIn .25s ease; }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(-3px); } to { opacity: 1; transform: translateY(0); } }
  .field.invalid .input-icon { color: #EF4444; background: #FEF2F2; border-color: #FCA5A5; }
  .field.invalid .login-input { border-color: #FCA5A5; }
  @keyframes shakeX { 0%, 100% { transform: translateX(0); } 20% { transform: translateX(-5px); } 40% { transform: translateX(5px); } 60% { transform: translateX(-3px); } 80% { transform: translateX(3px); } }
  .field.invalid { animation: shakeX .4s ease; }

  .btn-masuk {
    position: relative; display: flex; align-items: center; justify-content: center; gap: 9px; width: 100%; height: 48px;
    background: linear-gradient(135deg, #1D4ED8 0%, #2563EB 50%, #0EA5E9 100%);
    background-size: 160% auto; background-position: 0% 50%;
    color: #fff; border: none; border-radius: 10px;
    font-size: 15px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
    font-family: inherit; cursor: pointer; overflow: hidden;
    transition: background-position .35s ease, box-shadow .25s, transform .2s;
  }
  .btn-masuk:hover { background-position: 100% 50%; box-shadow: 0 14px 26px -12px rgba(37,99,235,.7); transform: translateY(-1px); }
  .btn-masuk:active { transform: translateY(0) scale(.995); }
  .btn-masuk:disabled { background: #93C5FD; background-size: auto; cursor: not-allowed; box-shadow: none; transform: none; }
  .btn-masuk::after {
    content: ""; position: absolute; top: 0; left: -70%; width: 45%; height: 100%;
    background: linear-gradient(115deg, transparent, rgba(255,255,255,.4), transparent);
    transform: skewX(-20deg); transition: left .6s; pointer-events: none;
  }
  .btn-masuk:hover::after { left: 130%; }
  /* Spinner absolute → tidak mengubah ukuran tombol / tidak ada layout shift */
  .btn-masuk .spinner {
    position: absolute; left: 20px; top: 50%; margin-top: -8.5px; width: 17px; height: 17px;
    border-radius: 50%; border: 2px solid rgba(255,255,255,.45); border-top-color: #fff;
    animation: spin .7s linear infinite; opacity: 0; transition: opacity .2s;
  }
  .btn-masuk.loading .spinner { opacity: 1; }
  @keyframes spin { to { transform: rotate(360deg); } }

  .login-note { font-size: 12.5px; color: #94A3B8; text-align: center; margin-top: 22px; line-height: 1.7; }
  .login-note strong { color: #475569; }

  /* ===== Footer ===== */
  .login-footer { text-align: center; padding: 16px 20px 22px; font-size: 12px; color: #94A3B8; line-height: 1.7; }
  .login-footer strong { color: #2563EB; }

  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
      animation-duration: .01ms !important; animation-iteration-count: 1 !important;
      transition-duration: .01ms !important; animation-delay: 0s !important;
    }
    .illust .deco, .illust-wrap img, .ill-chip, .illust-wrap::before, .illust .ambient { animation: none !important; }
    .illust .ambient { transform: none !important; }
  }

  /* ===== Responsive: desktop sedang ===== */
  @media (max-width: 1200px) {
    .login-main { grid-template-columns: minmax(0, 1.5fr) minmax(340px, 1fr); gap: 40px; padding: 26px 30px 12px; }
    .chip-c { display: none; }
  }

  /* ===== Responsive: tablet ===== */
  @media (max-width: 992px) {
    .login-main { grid-template-columns: 1fr; gap: 22px; padding: 20px 22px 8px; }
    .mob-brand { display: block; margin-bottom: 4px; }
    .card-head .ch-logo, .ch-fb { display: none; }
    .illust { min-height: clamp(240px, 36vh, 320px); border-radius: 22px; }
    .illust-wrap img { max-width: 86%; max-height: 260px; animation: none; }
    .illust-wrap::before { animation: none; }
    .illust .deco.d3, .illust .deco.d4, .illust .ambient { display: none; }
    .ill-chip { display: none; }
    .login-side { justify-content: center; }
    .login-card { max-width: 420px; }
  }

  /* ===== Responsive: mobile ===== */
  @media (max-width: 576px) {
    .login-main { padding: 14px 14px 6px; gap: 18px; }
    .mob-brand img { width: 40px; margin-bottom: 7px; }
    .illust { min-height: 210px; border-radius: 16px; }
    .illust-wrap img { max-height: 200px; }
    .login-card { padding: 28px 22px; border-radius: 18px; }
    .login-card:hover { transform: none; }
    .input-group { height: 46px; }
    .login-footer { padding: 14px 16px 18px; font-size: 11.5px; }
  }
</style>
</head>
<body>

<div class="login-page">

  <div class="mob-brand">
    <img src="{{ asset('assets/images/logo-rskk.png') }}" alt="Logo RSKK" onerror="this.style.display='none'">
    <div class="mb-name">SIMPEG RSKK</div>
    <div class="mb-sub">Sistem Informasi Manajemen Kepegawaian</div>
  </div>

  <main class="login-main">

    {{-- ===== Kolom kiri: hero visual RSKK + animasi premium ===== --}}
    <section class="illust">
      <span class="ambient"></span>
      <span class="deco d1"></span>
      <span class="deco d2"></span>
      <span class="deco d3"></span>
      <span class="deco d4"></span>
      <span class="grain"></span>

      <div class="illust-wrap">
        <img id="maskot" src="{{ $loginPhotoUrl ?? asset('assets/images/login-maskot.png') }}" alt="Ilustrasi RSKK"
             decoding="async"
             onerror="this.style.display='none';document.getElementById('ph-empty').style.display='flex'">
        <div class="ph-empty" id="ph-empty">
          <svg style="width:34px;height:34px;color:#93C5FD" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75A2.25 2.25 0 016.75 4.5h10.5A2.25 2.25 0 0119.5 6.75v10.5A2.25 2.25 0 0117.25 19.5H6.75A2.25 2.25 0 014.5 17.25V6.75zM6.75 8.25h10.5M8.25 12h7.5M8.25 15h4.5"/></svg>
          <span>Ilustrasi RSKK</span>
        </div>
      </div>

      <div class="ill-chip chip-a">
        <span class="ic" style="background:#EFF6FF;color:#2563EB">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </span>
        <span>
          <div class="tt">Data Pegawai</div>
          <div class="st">Terpusat &amp; terintegrasi</div>
        </span>
      </div>

      <div class="ill-chip chip-b">
        <span class="ic" style="background:#ECFEFF;color:#0891B2">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.6-2.6A11.95 11.95 0 0112 21a11.95 11.95 0 01-8.6-13.6A12 12 0 018 2.2 11.95 11.95 0 0112 3a11.95 11.95 0 014-.8A12 12 0 0120.6 9.4z"/></svg>
        </span>
        <span>
          <div class="tt">Persetujuan Daring</div>
          <div class="st">Ajukan &amp; setujui secara online</div>
        </span>
      </div>

      <div class="ill-chip chip-c">
        <span class="ic" style="background:#EEF2FF;color:#4F46E5">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </span>
        <span>
          <div class="tt">Dokumen Digital</div>
          <div class="st">Arsip aman dalam satu tempat</div>
        </span>
      </div>
    </section>

    {{-- ===== Kolom kanan: card login compact ===== --}}
    <section class="login-side">
      <div class="login-card">

        <div class="card-head">
          <div class="ch-brand stg" style="--d:.10s">
            <img class="ch-logo" src="{{ asset('assets/images/logo-rskk.png') }}"
                 onerror="this.style.display='none';document.getElementById('ch-fb').style.display='flex'"
                 alt="Logo RSKK">
            <div class="ch-fb" id="ch-fb">
              <svg style="width:26px;height:26px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            </div>
          </div>
          <h1 class="stg" style="--d:.18s">SIMPEG RSKK</h1>
          <div class="ch-sub stg" style="--d:.24s">Sistem Informasi Manajemen Kepegawaian</div>

          <div class="ch-welcome">
            <h2 class="stg" style="--d:.36s">Selamat Datang!</h2>
            <p class="stg" style="--d:.42s">Silakan masuk untuk mengakses akun Anda</p>
          </div>
        </div>

        @if ($errors->any())
          <div class="alert-error" role="alert" aria-live="polite">
            <svg style="width:16px;height:16px;flex:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.01M9.6 3.75l-5.4 9.3A2 2 0 005.9 17h12.2a2 2 0 001.7-2.95l-5.4-9.3A2 2 0 009.6 3.75z"/></svg>
            <span>{{ $errors->first() }}</span>
          </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="login-form" novalidate>
          @csrf

          <div class="field stg" id="field-nip" style="--d:.48s">
            <label class="flabel" for="nip">Username / NIP</label>
            <div class="input-group">
              <span class="input-icon">
                <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a7.5 7.5 0 0115 0"/></svg>
              </span>
              <input id="nip" type="text" name="nip" value="{{ old('nip') }}" required autofocus
                     autocomplete="username" placeholder="Masukkan NIP / Username" spellcheck="false" class="login-input">
            </div>
            <span class="err" id="err-nip">NIP / Username wajib diisi.</span>
          </div>

          <div class="field stg" id="field-password" style="--d:.54s">
            <label class="flabel" for="password">Password</label>
            <div class="input-group">
              <span class="input-icon">
                <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3v1.5m-1.5 10.5h-10.5a1.5 1.5 0 01-1.5-1.5v-7.5a1.5 1.5 0 011.5-1.5h10.5a1.5 1.5 0 011.5 1.5v7.5a1.5 1.5 0 01-1.5 1.5zM12 13.5v2.25a.75.75 0 001.5 0V13.5a1.5 1.5 0 10-1.5 0z"/></svg>
              </span>
              <div class="pwd-wrap">
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       placeholder="Masukkan password" class="login-input pad-r">
                <button type="button" class="eye-btn" id="pt" aria-label="Tampilkan password">
                  <span class="eye-open"><svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.06 12.35a1 1 0 010-.7C3.8 8.25 7.5 5 12 5s8.2 3.25 9.94 6.65a1 1 0 010 .7C20.2 15.75 16.5 19 12 19s-8.2-3.25-9.94-6.35zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span>
                  <span class="eye-off" style="display:none"><svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 5.1A9.6 9.6 0 0112 5c4.5 0 8.2 3.25 9.94 6.65a1 1 0 010 .7 10.8 10.8 0 01-2.6 3.4M6 6a10.7 10.7 0 00-3.94 5.65 1 1 0 000 .7C3.8 15.75 7.5 19 12 19a9.3 9.3 0 003.6-.7"/></svg></span>
                </button>
              </div>
            </div>
            <span class="err" id="err-password">Password wajib diisi.</span>
          </div>

          <div class="stg" style="--d:.60s">
            <button type="submit" class="btn-masuk" id="btn">
              <span class="spinner"></span><span class="lbl">Masuk</span>
            </button>
          </div>
        </form>

        <div class="login-note stg" style="--d:.66s">
          Pastikan NIP/Username dan password Anda benar.<br>
          Kesulitan masuk? Hubungi <strong>Admin RSKK</strong>.
        </div>

      </div>
    </section>

  </main>

  <footer class="login-footer">
    &copy; {{ date('Y') }} <strong>SIMPEG RSKK</strong> · Sistem Informasi Manajemen Kepegawaian
  </footer>

</div>

<script>
  // ===== Password toggle (fungsi existing dipertahankan) =====
  var pt = document.getElementById('pt');
  pt.addEventListener('click', function () {
    var i = document.getElementById('password');
    var show = i.type === 'password';
    i.type = show ? 'text' : 'password';
    this.querySelector('.eye-open').style.display = show ? 'none' : '';
    this.querySelector('.eye-off').style.display = show ? '' : 'none';
    this.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    i.focus();
  });

  // ===== Validasi + loading state (fungsi existing dipertahankan) =====
  var form = document.getElementById('login-form');
  var nip = document.getElementById('nip');
  var pw = document.getElementById('password');
  var btn = document.getElementById('btn');

  function mark(fieldEl, errId, input, show) {
    document.getElementById(fieldEl).classList.toggle('invalid', show);
    document.getElementById(errId).classList.toggle('show', show);
  }

  nip.addEventListener('input', function () { if (nip.value.trim()) mark('field-nip', 'err-nip', nip, false); });
  pw.addEventListener('input', function () { if (pw.value) mark('field-password', 'err-password', pw, false); });

  form.addEventListener('submit', function (e) {
    if (btn.classList.contains('loading')) { e.preventDefault(); return; }
    var bad = false;
    if (!nip.value.trim()) { mark('field-nip', 'err-nip', nip, true); bad = true; }
    if (!pw.value) { mark('field-password', 'err-password', pw, true); bad = true; }
    if (bad) { e.preventDefault(); return; }
    btn.classList.add('loading');
    btn.disabled = true;
    btn.setAttribute('aria-busy', 'true');
    btn.querySelector('.lbl').textContent = 'Memproses...';
  });

  // ===== Sapaan mengikuti waktu (pagí/siang/sore/malam) =====
  (function () {
    var h = new Date().getHours();
    var greet = h < 11 ? 'Selamat Pagi!' : h < 15 ? 'Selamat Siang!' : h < 19 ? 'Selamat Sore!' : 'Selamat Malam!';
    var gh = document.querySelector('.ch-welcome h2');
    if (gh) gh.textContent = greet;
  })();

  // ===== Parallax halus (desktop saja, maks 5px, hormati reduced-motion) =====
  (function () {
    var ill = document.querySelector('.illust');
    if (!ill) return;
    var fine = window.matchMedia('(pointer:fine)').matches;
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!fine || reduce || window.innerWidth < 993) return;

    var raf = null;
    document.addEventListener('mousemove', function (e) {
      if (raf) return;
      raf = requestAnimationFrame(function () {
        raf = null;
        var r = ill.getBoundingClientRect();
        if (!r.width || !r.height) return;
        var cx = (e.clientX - (r.left + r.width / 2)) / (r.width / 2);
        var cy = (e.clientY - (r.top + r.height / 2)) / (r.height / 2);
        ill.style.setProperty('--px', (Math.max(-1, Math.min(1, cx)) * 5).toFixed(2) + 'px');
        ill.style.setProperty('--py', (Math.max(-1, Math.min(1, cy)) * 5).toFixed(2) + 'px');
      });
    });
  })();
</script>
</body>
</html>
