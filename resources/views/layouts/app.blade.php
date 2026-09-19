<!DOCTYPE html>
@php $__app = App\Models\Settings::get('app_name', 'SIMPEG RSKK'); @endphp
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', $__app) — {{ $__app }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
  /* ============================================================
     BASE RESET (backup: menjaga stabilitas walau Tailwind CDN
     gagal termuat — box-sizing, border tombol, ukuran font input)
     ============================================================ */
  *, *::before, *::after{ box-sizing:border-box; }
  button, input, select, textarea{ font-family:inherit; }
  button{ border:none; }

  /* ============================================================
     SIMPEG RSKK — ENTERPRISE HEALTHCARE PERSONNEL SYSTEM
     Design tokens & global system (Clean Corporate)
     ============================================================ */
  :root{
    /* Primary — professional blue */
    --blue-900:#1E3A8A; --blue-800:#1E40AF; --blue-700:#1D4ED8; --blue-600:#2563EB; --blue-500:#3B82F6;
    --blue-100:#DBEAFE; --blue-50:#EFF6FF;
    /* Navy — aksen tegas enterprise */
    --navy-900:#0C1F3D; --navy-800:#122A52; --navy-700:#1A3A6E;
    /* Secondary — teal/cyan hanya aksen kecil */
    --teal-700:#0F766E; --teal-600:#0D9488; --teal-500:#14B8A6;
    --teal-100:#CCFBF1; --teal-50:#F0FDFA; --teal-300:#5EEAD4;
    --cyan-600:#0891B2; --cyan-50:#ECFEFF;
    /* Teks & latar — slate netral pemerintah */
    --ink-900:#101828; --ink-700:#1F2937; --ink-500:#475467; --ink-300:#98A2B3; --ink-200:#D0D5DD;
    --paper:#F6F9FC; --line:#E4E7EC; --line-soft:#EEF1F6;
    --surface:#FFFFFF;
    /* Status — konsisten seluruh sistem */
    --green-600:#039855; --green-100:#A7F3D0; --green-50:#ECFDF5;
    --amber-600:#B54708; --amber-200:#FDE68A; --amber-100:#FDD68A; --amber-50:#FFFAEB;
    --red-600:#D92D20; --red-100:#FECDCA; --red-50:#FEF3F2;
    --gray-100:#EAECF0; --gray-200:#D0D5DD;
    --shadow-sm:0 1px 2px rgba(16,24,40,.06);
    --shadow-md:0 12px 30px -12px rgba(16,24,40,.18);

    /* Layout geometry */
    --sidebar-w:256px;
    --sidebar-w-collapsed:80px;
    --topbar-h:64px;
    --content-pad:24px;

    /* Typography */
    --font-sans:'Inter',ui-sans-serif,system-ui,sans-serif;
    --font-head:'Poppins','Inter',ui-sans-serif,system-ui,sans-serif;

    --z-sidebar:40; --z-backdrop:35; --z-topbar:30; --z-dropdown:50; --z-modal:90;
  }

  /* ---------- Base ---------- */
  html{ -webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility; }
  body{ font-family:var(--font-sans); background:var(--paper); color:var(--ink-900); font-size:14px; line-height:1.55; overflow-x:hidden; min-height:100vh; }
  ::selection{ background:rgba(37,99,235,.14); }
  a{ transition:color .15s ease; text-decoration:none; }

  /* ---------- Typography scale ---------- */
  .font-head{ font-family:var(--font-head); }
  .t-h1{ font-family:var(--font-head); font-size:26px; font-weight:700; letter-spacing:-.015em; color:var(--navy-900); line-height:1.25; }
  .t-h2{ font-family:var(--font-head); font-size:18px; font-weight:700; letter-spacing:-.01em; color:var(--navy-900); }
  .t-h3{ font-family:var(--font-head); font-size:14px; font-weight:600; color:var(--ink-900); }
  .t-body{ font-size:14px; color:var(--ink-900); }
  .t-sub{ font-size:13px; color:var(--ink-500); }
  .t-meta{ font-size:11.5px; color:var(--ink-300); }

  /* ============================================================
     SHELL — Sidebar / Main / Topbar
     ============================================================ */
  .app-shell{ min-height:100vh; }

  /* --- Sidebar (fixed) --- */
  .side{ width:var(--sidebar-w); position:fixed; inset:0 auto 0 0; z-index:var(--z-sidebar);
         background:var(--surface); border-right:1px solid var(--line);
         display:flex; flex-direction:column; overflow:hidden;
         transition:width .22s ease, transform .22s ease; }
  .side .navtext, .side .navgroup, .side .side-user .user-text{ transition:opacity .12s; }
  .side .navtext{ min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .side-nav{ flex:1; overflow-y:auto; overflow-x:hidden; min-height:0; padding:12px 0 6px; }
  .side.collapsed{ width:var(--sidebar-w-collapsed); }
  .side.collapsed .navtext, .side.collapsed .navgroup, .side.collapsed .side-user .user-text{ display:none; }

  .navgroup{ font-size:10px; font-weight:700; letter-spacing:.09em; color:var(--ink-400,var(--ink-300)); margin:16px 22px 6px; text-transform:uppercase; white-space:nowrap; }
  .navlink{ position:relative; display:flex; align-items:center; gap:12px; padding:9.5px 14px; margin:2px 12px; border-radius:10px;
            font-size:13.5px; font-weight:500; color:var(--ink-500); white-space:nowrap; transition:background .14s,color .14s,transform .14s,box-shadow .14s; }
  .navlink .ic{ width:18px; height:18px; flex:none; color:var(--ink-300); transition:color .14s, transform .18s ease; }
  .navlink:hover{ background:#F4F6FA; color:var(--ink-900); }
  .navlink:hover .ic{ color:var(--blue-600); transform:scale(1.12); }
  .navlink:active{ transform:scale(.98); }
  .navlink.active{ background:var(--blue-50); color:var(--blue-700); font-weight:600; box-shadow:inset 3px 0 0 var(--blue-600); }
  .navlink.active .ic{ color:var(--blue-600); }
  .nav-badge{ margin-left:auto; font-size:10px; font-weight:700; padding:1.5px 7px; border-radius:100px;
              background:var(--blue-600); color:#fff; box-shadow:0 0 0 2px #fff; transition:transform .15s ease; }
  .navlink:hover .nav-badge{ transform:scale(1.1); }
  .side.collapsed .navlink{ justify-content:center; padding:10px 0; margin:2px 9px; }
  .side.collapsed .nav-badge{ display:none; }

  .side-nav{ scrollbar-width:thin; scrollbar-color:var(--line) transparent; }
  .side-nav::-webkit-scrollbar{ width:6px; }
  .side-nav::-webkit-scrollbar-thumb{ background:var(--line); border-radius:8px; }
  .side-nav::-webkit-scrollbar-thumb:hover{ background:#CBD5E1; }
  .side-nav::-webkit-scrollbar-track{ background:transparent; }

  /* --- Sidebar header (brand) --- */
  .side-head{ padding:14px 17px 13px; display:flex; flex-direction:column; gap:9px; flex:none;
              border-bottom:1px solid var(--line);
              background:linear-gradient(180deg,rgba(239,246,255,.55),rgba(255,255,255,0) 72%); }
  .brand-plate{ height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center;
                padding:5px 9px; overflow:hidden; flex:none;
                background:linear-gradient(160deg,#FFFFFF 0%,#F3F8FF 58%,#E9F2FF 100%);
                border:1px solid rgba(37,99,235,.12);
                box-shadow:0 1px 2px rgba(16,24,40,.05), inset 0 1px 0 #fff;
                transition:transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
  .brand-plate img{ max-width:100%; max-height:100%; width:auto; height:auto; object-fit:contain; display:block; }
  .brand-plate .ph{ width:28px; height:28px; display:none; align-items:center; justify-content:center;
                    border-radius:8px; background:var(--blue-600); color:#fff; font-family:var(--font-head);
                    font-size:16px; font-weight:700; }
  .side-head:hover .brand-plate{ transform:translateY(-1px); border-color:rgba(37,99,235,.24);
                                 box-shadow:0 10px 22px -10px rgba(37,99,235,.42), inset 0 1px 0 #fff; }

  .brand-text{ min-width:0; }
  .brand-title{ font-family:var(--font-head); font-size:16.5px; font-weight:700; letter-spacing:-.3px;
                color:var(--navy-900); line-height:1.2; }
  .brand-sub{ margin-top:6px; font-size:10.5px; font-weight:500; line-height:1.35; letter-spacing:.005em;
              color:#64748B; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

  .side.collapsed .side-head{ padding:12px 8px; }
  .side.collapsed .brand-text{ display:none; }

  @keyframes brandPlateIn{ from{ opacity:0; transform:scale(.96); } to{ opacity:1; transform:scale(1); } }
  @keyframes brandTextIn{ from{ opacity:0; transform:translateX(-4px); } to{ opacity:1; transform:translateX(0); } }
  html.brand-anim .brand-plate{ animation:brandPlateIn .6s cubic-bezier(.22,1,.36,1) .05s backwards; }
  html.brand-anim .brand-text{ animation:brandTextIn .6s cubic-bezier(.22,1,.36,1) .16s backwards; }
  @media (prefers-reduced-motion:reduce){
    html.brand-anim .brand-plate, html.brand-anim .brand-text{ animation:none; }
  }

  /* --- Sidebar footer (user + logout) --- */
  .side-foot{ padding:10px 10px 12px; display:flex; flex-direction:column; gap:2px; flex:none;
              border-top:1px solid var(--line); }
  .side-user{ display:flex; align-items:center; gap:10px; padding:8px 8px; border-radius:10px;
              transition:background .14s; }
  .side-user:hover{ background:#F4F6FA; }
  .side-user .user-chevron{ flex:none; color:var(--ink-300); transition:transform .15s, color .15s; }
  .side-user:hover .user-chevron{ color:var(--blue-600); transform:translateY(1px); }
  .side-logout{ display:flex; align-items:center; gap:12px; padding:9px 10px 9px 12px; border-radius:10px;
                font-size:13px; font-weight:500; color:var(--red-600); transition:background .14s, color .14s; }
  .side-logout .ic{ width:18px; height:18px; flex:none; color:var(--red-500); }
  .side-logout:hover{ background:#FEF2F2; color:var(--red-700); }
  .side.collapsed .side-user{ justify-content:center; padding:8px 0; }
  .side.collapsed .side-user .user-chevron{ display:none; }
  .side.collapsed .side-logout{ justify-content:center; padding:10px 0; }

  /* --- Main column --- */
  .main-wrap{ margin-left:var(--sidebar-w); min-height:100vh; display:flex; flex-direction:column; transition:margin .22s ease; }
  .app-shell.collapsed .main-wrap{ margin-left:var(--sidebar-w-collapsed); }

  /* --- Topbar --- */
  .topbar{ position:sticky; top:0; z-index:var(--z-topbar); height:var(--topbar-h); flex:none;
           background:rgba(255,255,255,.94); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);
           border-bottom:1px solid var(--line); box-shadow:0 1px 2px rgba(16,24,40,.03);
           display:flex; align-items:center; gap:12px; padding:0 var(--content-pad); }
  .icon-btn{ width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center;
             color:var(--ink-500); cursor:pointer; transition:.15s; border:1px solid transparent; background:transparent; }
  .icon-btn:hover{ background:var(--blue-50); color:var(--blue-600); transform:translateY(-1px); }
  .icon-btn:active{ transform:scale(.94); }
  .icon-btn svg{ transition:transform .15s ease; }
  .icon-btn:hover svg{ transform:scale(1.1); }

  /* Breadcrumb (topbar, kiri) */
  .crumb{ display:flex; align-items:center; gap:8px; font-size:12.5px; font-weight:500; color:var(--ink-300); white-space:nowrap; }
  .crumb .sep{ color:var(--ink-200); }
  .crumb .cur{ color:var(--ink-900); font-weight:600; }

  /* Global search (topbar, tengah) */
  .topbar-search{ position:relative; }
  .topbar-search input{ width:min(420px,38vw); padding:9px 14px 9px 38px; border:1px solid var(--line); border-radius:10px;
                        background:var(--paper); font-size:13px; color:var(--ink-900); outline:none; transition:.15s; }
  .topbar-search input::placeholder{ color:var(--ink-300); }
  .topbar-search input:focus{ border-color:var(--blue-500); background:#fff; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
  .topbar-search .si{ position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--ink-300); }
  .gs-res{ position:absolute; left:0; right:0; top:calc(100% + 4px); background:#fff; border:1px solid var(--line); border-radius:10px;
           box-shadow:var(--shadow-md); max-height:340px; overflow-y:auto; display:none; z-index:60; }
  .gs-res.has{ display:block; }
  .gs-res-item{ display:flex; align-items:center; gap:10px; padding:9px 14px; cursor:pointer; text-decoration:none; color:inherit; }
  .gs-res-item:hover{ background:var(--blue-50); }
  .gs-avatar{ width:32px; height:32px; border-radius:50%; background:var(--blue-500); color:#fff; font-size:11px; font-weight:600;
              display:flex; align-items:center; justify-content:center; flex:none; }
  .gs-meta{ min-width:0; flex:1; }
  .gs-name{ font-size:13px; font-weight:600; color:var(--ink-900); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .gs-sub{ font-size:11.5px; color:var(--ink-500); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .gs-empty{ padding:12px; text-align:center; font-size:12px; color:var(--ink-500); }

  /* Dropdown */
  .dropdown{ position:relative; }
  .dropdown-menu{ position:absolute; right:0; top:calc(100% + 10px); width:320px; background:#fff;
                  border:1px solid var(--line); border-radius:14px; box-shadow:var(--shadow-md);
                  opacity:0; transform:translateY(-6px) scale(.98); pointer-events:none; transition:.16s ease; z-index:var(--z-dropdown); overflow:hidden; }
  .dropdown.open .dropdown-menu{ opacity:1; transform:translateY(0) scale(1); pointer-events:auto; }
  .menu-title{ font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--ink-300); }
  .menu-item{ display:flex; align-items:center; gap:10px; width:100%; text-align:left; padding:10px 16px; font-size:13px;
              color:var(--ink-700); } .menu-item:hover{ background:var(--blue-50); color:var(--blue-600); }
  .divider{ height:1px; background:var(--line-soft); }

  /* --- Content --- */
  .main-content{ flex:1; width:100%; padding:28px var(--content-pad) 48px; }
  .main-inner{ max-width:1480px; margin:0 auto; width:100%; }
  .profile-wrap{ max-width:1180px; margin:0 auto; }

  /* Page head */
  .page-head{ display:flex; align-items:flex-start; justify-content:space-between; gap:18px; flex-wrap:wrap; margin-bottom:24px; }
  .page-title, h1.page-title{ font-family:var(--font-head); font-size:28px; font-weight:700; letter-spacing:-.022em; color:var(--navy-900); line-height:1.25; }
  .page-desc{ font-size:13.5px; color:var(--ink-500); margin-top:5px; max-width:640px; }

  /* ============================================================
     COMPONENTS
     ============================================================ */
  .card{ background:var(--surface); border:1px solid var(--line); border-radius:14px; box-shadow:var(--shadow-sm);
         transition:box-shadow .18s ease, border-color .18s ease, transform .18s ease; }
  .card-hover:hover{ box-shadow:var(--shadow-md); transform:translateY(-1px); }

  .btn{ display:inline-flex; align-items:center; justify-content:center; gap:7px; font-weight:600; border-radius:10px;
        padding:9px 16px; font-size:13px; line-height:1.2; cursor:pointer; transition:background .16s ease, border-color .16s ease, color .16s ease, box-shadow .16s ease, transform .16s ease; white-space:nowrap; }
  .btn-primary{ background:var(--blue-600); color:#fff; box-shadow:0 1px 2px rgba(16,24,40,.1); }
  .btn-primary::after, .btn-danger::after{ content:''; position:absolute; top:0; bottom:0; left:-70%; width:45%;
    background:linear-gradient(100deg, transparent, rgba(255,255,255,.26), transparent);
    transform:skewX(-22deg); transition:left .55s cubic-bezier(.22,1,.36,1); pointer-events:none; }
  .btn-primary:hover::after, .btn-danger:hover::after{ left:135%; }
  .btn-primary{ position:relative; overflow:hidden; }
  .btn-primary:hover{ background:var(--blue-700); transform:translateY(-1px); box-shadow:0 6px 16px -8px rgba(37,99,235,.55); }
  .btn-outline{ background:#fff; border:1px solid var(--line); color:var(--ink-700); }
  .btn-outline:hover{ border-color:var(--blue-500); color:var(--blue-600); transform:translateY(-1px); box-shadow:0 6px 14px -10px rgba(37,99,235,.45); }
  .btn-ghost{ background:transparent; color:var(--ink-700); }
  .btn-ghost:hover{ background:var(--blue-50); color:var(--blue-600); }
  .btn-danger{ background:var(--red-600); color:#fff; position:relative; overflow:hidden; }
  .btn-danger:hover{ background:#B42318; transform:translateY(-1px); }
  .btn-sm{ padding:6px 12px; font-size:12px; border-radius:9px; }
  .btn:active{ transform:scale(.98); }
  .btn-primary:active, .btn-outline:active, .btn-ghost:active, .btn-danger:active{ transform:scale(.98); }
  .btn:focus-visible, .btn-primary:focus-visible, .btn-outline:focus-visible, .btn-ghost:focus-visible, .btn-danger:focus-visible,
  .icon-btn:focus-visible, .navlink:focus-visible, .tab-btn:focus-visible, .chip:focus-visible{
    outline:none; box-shadow:0 0 0 3px rgba(37,99,235,.3); }

  .badge{ display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:3px 10px;
          border-radius:100px; border:1px solid; white-space:nowrap; line-height:1.35; }
  .dot{ width:6px; height:6px; border-radius:50%; flex:none; }

  .stat-card{ background:var(--surface); border:1px solid var(--line); border-radius:14px; padding:18px 20px; box-shadow:var(--shadow-sm);
            transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
  .stat-card:hover{ transform:translateY(-2px); border-color:var(--blue-200); box-shadow:0 16px 32px -16px rgba(16,24,40,.18); }
  .stat-card .stat-ic{ width:40px; height:40px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex:none;
                       transition:transform .2s ease; }
  .stat-card:hover .stat-ic{ transform:scale(1.1) rotate(-4deg); }
  .stat-card .stat-val{ font-size:25px; font-weight:700; letter-spacing:-.02em; color:var(--ink-900); line-height:1.1; }

  /* --- Dashboard premium: KPI glass + ikon gradient + glow --- */
  .stat-card.prem{ position:relative; overflow:hidden; border-radius:16px; }
  .stat-card.prem::after{ content:''; position:absolute; right:-30px; top:-30px; width:110px; height:110px; border-radius:50%;
                          background:radial-gradient(circle, var(--c1), transparent 68%);
                          opacity:.16; pointer-events:none; transition:opacity .25s ease; }
  .stat-card.prem:hover::after{ opacity:.3; }
  .stat-card.prem .stat-ic{ width:44px; height:44px; border-radius:13px; color:#fff;
                            background:linear-gradient(135deg, var(--c1), var(--c2));
                            box-shadow:0 8px 18px -8px var(--c1); }
  .stat-card.prem .stat-val{ font-variant-numeric:tabular-nums; }
  .stat-card.prem:hover .stat-ic{ transform:scale(1.1) rotate(-4deg); }

  /* --- Kartu mini-aksi (Perlu Tindakan) --- */
  .min-act{ position:relative; overflow:hidden; border-radius:16px; }
  .min-act .act-ic{ width:44px; height:44px; border-radius:13px; color:#fff; flex:none;
                    background:linear-gradient(135deg, var(--h1), var(--h2));
                    box-shadow:0 8px 18px -8px var(--h1); transition:transform .2s ease; }
  .min-act:hover .act-ic{ transform:scale(1.08) rotate(-4deg); }
  .min-act .chev{ opacity:.35; transition:opacity .18s ease, transform .18s ease; color:var(--ink-400); }
  .min-act:hover .chev{ opacity:1; transform:translateX(3px); }

  /* --- Row hover elegan (daftar mutasi/diklat/pengajuan) --- */
  .hrow{ transition:background .16s ease, transform .16s ease, border-color .16s ease; }
  .hrow:hover{ background:#F6F9FF; transform:translateX(2px); border-color:#CDE3FF !important; }

  /* --- Dot notifikasi belum dibaca — pulse halus --- */
  @keyframes dotPulse{ 0%,100%{ box-shadow:0 0 0 0 rgba(37,99,235,.35); } 50%{ box-shadow:0 0 0 4px rgba(37,99,235,0); } }
  .dot-live{ animation:dotPulse 1.8s ease-in-out infinite; }

  /* --- Donut masuk halus --- */
  @keyframes donutIn{ from{ opacity:0; transform:scale(.92); } to{ opacity:1; transform:scale(1); } }
  .donut{ animation:donutIn .6s cubic-bezier(.22,1,.36,1) both; }

  /* --- Progress bar: sapuan cahaya halus --- */
  .progress > div{ position:relative; overflow:hidden; }
  .progress > div::after{ content:''; position:absolute; inset:0; transform:translateX(-100%);
                          background:linear-gradient(90deg, transparent, rgba(255,255,255,.35), transparent);
                          animation:barSweep 2.6s ease-in-out infinite; }
  @keyframes barSweep{ 60%,100%{ transform:translateX(100%); } }

  .field-row{ display:flex; justify-content:space-between; gap:12px; padding:9px 0; border-bottom:1px solid var(--line-soft); }
  .field-row:last-child{ border-bottom:none; }

  /* Table — enterprise */
  .table-wrap{ overflow-x:auto; }
  table.tbl, table.tbl-striped{ border-collapse:collapse; width:100%; }
  .table-th{ font-size:11px; font-weight:600; letter-spacing:.05em; text-transform:uppercase; color:var(--ink-300);
             text-align:left; padding:11px 16px; background:#FAFBFD; border-bottom:1px solid var(--line); white-space:nowrap; }
  .table-td{ padding:11px 16px; font-size:13.5px; vertical-align:middle; color:var(--ink-700); }
  tr.row-line{ border-top:1px solid var(--line-soft); transition:background .12s; }
  tr.row-line:hover{ background:#F7F9FC; }
  .table-td .trow-main{ font-weight:600; color:var(--ink-900); }
  .table-td .trow-sub{ font-size:12px; color:var(--ink-300); }

  /* Tabs */
  .tab-bar{ display:flex; gap:2px; overflow-x:auto; border-bottom:1px solid var(--line); -webkit-overflow-scrolling:touch; }
  .tab-btn{ padding:12px 16px; font-size:13.5px; font-weight:500; color:var(--ink-500); border-bottom:2px solid transparent;
            white-space:nowrap; transition:.15s; flex:none; }
  .tab-btn:hover{ color:var(--ink-900); }
  .tab-btn.active{ color:var(--blue-600); border-bottom-color:var(--blue-600); font-weight:600; }

  /* Form */
  .input, select.input, textarea.input{ display:block; width:100%; box-sizing:border-box; padding:9.5px 13px; border:1px solid var(--line); border-radius:10px; background:#fff; font-size:13.5px; color:var(--ink-900); transition:.15s; }
  .input:focus{ outline:none; border-color:var(--blue-500); box-shadow:0 0 0 3px rgba(37,99,235,.12); }
  /* Konsistensi: input/select/textarea gaya lama ikut focus ring */
  input:not(.input):focus, select:not(.input):focus, textarea:not(.input):focus{ outline:none; box-shadow:0 0 0 3px rgba(37,99,235,.10); }
  label.flabel{ display:block; font-size:12.5px; font-weight:600; color:var(--ink-700); margin-bottom:6px; }
  label.flabel .req{ color:var(--red-600); }

  /* --- Security/password form (Pengaturan) --- */
  .field-error{ display:flex; align-items:flex-start; gap:6px; margin-top:7px; font-size:12px; font-weight:500; color:var(--red-600); line-height:1.4; }
  .field-hint{ font-size:12px; color:var(--ink-400); margin-top:6px; }
  .pw-input{ position:relative; }
  .pw-input .input{ padding-right:44px; }
  .pw-eye{ position:absolute; right:5px; top:50%; transform:translateY(-50%); width:36px; height:36px;
           display:flex; align-items:center; justify-content:center; border:0; background:none; cursor:pointer;
           color:var(--ink-400); border-radius:9px; transition:.14s; }
  .pw-eye:hover{ color:var(--blue-600); background:var(--blue-50); }
  .pw-strength{ display:flex; align-items:center; gap:10px; margin-top:8px; }
  .pw-strength .bar{ flex:1; height:5px; border-radius:999px; background:var(--line-soft); overflow:hidden; }
  .pw-strength .bar > i{ display:block; height:100%; width:0; border-radius:999px; transition:width .3s ease, background-color .3s ease; }
  .pw-strength .txt{ font-size:11.5px; font-weight:600; color:var(--ink-400); white-space:nowrap; }
  .fs-label{ font-size:12px; font-weight:600; color:var(--ink-700); }
  .fs-value{ font-size:12px; font-weight:700; color:var(--ink-400); }
  .strength-seg{ display:flex; gap:6px; }
  .strength-seg i{ flex:1; height:5px; border-radius:999px; background:var(--line-soft); transition:background .25s ease, opacity .25s ease; }
  .pw-match{ display:none; align-items:center; gap:6px; margin-top:7px; font-size:12px; font-weight:500; }
  .pw-match.ok{ color:var(--green-600); } .pw-match.bad{ color:var(--red-600); }
  .settings-card-head{ display:flex; align-items:flex-start; gap:14px; }
  .settings-card-head .shield{ width:42px; height:42px; border-radius:11px; flex:none; display:flex; align-items:center; justify-content:center;
                                background:var(--blue-50); color:var(--blue-600); }
  .settings-card-title{ font-family:var(--font-head); font-size:15px; font-weight:700; color:var(--ink-900); line-height:1.3; }
  .settings-card-sub{ font-size:12.5px; color:var(--ink-500); margin-top:3px; }
  .tips-list li{ display:flex; align-items:flex-start; gap:8px; font-size:12.5px; color:var(--ink-600); padding:3px 0; }
  .tips-list li svg{ width:13px; height:13px; flex:none; margin-top:2px; color:var(--blue-600); }
  @media (max-width:480px){ .settings-actions .btn{ width:100%; } }
  .lb-hidden{ display:none; }
  .lb-spin{ animation:lbspin .9s linear infinite; }
  @keyframes lbspin{ to{ transform:rotate(360deg); } }
  .btn:disabled, .btn[disabled]{ opacity:.6; cursor:not-allowed; }

  .alert{ display:flex; align-items:flex-start; gap:10px; padding:12px 15px; border-radius:11px; font-size:13.5px; border:1px solid; }
  .alert-success{ background:var(--green-50); color:var(--green-600); border-color:var(--green-100); }
  .alert-warning{ background:var(--amber-50); color:var(--amber-600); border-color:var(--amber-100); }
  .alert-danger{ background:var(--red-50); color:var(--red-600); border-color:var(--red-100); }
  .alert-info{ background:var(--blue-50); color:var(--blue-600); border-color:var(--blue-100); }

  .empty-state{ text-align:center; padding:40px 20px; color:var(--ink-500); }
  .empty-state .es-ic{ width:52px; height:52px; border-radius:14px; margin:0 auto 12px; display:flex; align-items:center; justify-content:center;
                        background:var(--blue-50); color:var(--blue-600); }

  .avatar{ border-radius:50%; object-fit:cover; border:2px solid #fff; box-shadow:var(--shadow-sm); }
  .avatar-zoom{ cursor:zoom-in; }
  /* Flatpickr — tema konsisten dengan design token aplikasi */
  .flatpickr-calendar{ border:1px solid var(--line); border-radius:14px; box-shadow:var(--shadow-md); font-family:var(--font-sans); font-size:13px; }
  .flatpickr-calendar.arrowTop:before{ border-bottom-color:var(--line); }
  .flatpickr-calendar.arrowTop:after{ border-bottom-color:#fff; }
  .flatpickr-calendar.arrowBottom:before{ border-top-color:var(--line); }
  .flatpickr-calendar.arrowBottom:after{ border-top-color:#fff; }
  .flatpickr-months .flatpickr-month{ color:var(--ink-900); }
  .flatpickr-current-month .flatpickr-monthDropdown-months{ font-weight:600; color:var(--ink-700); }
  .flatpickr-current-month input.cur-year{ font-weight:600; color:var(--ink-700); }
  .flatpickr-weekdays{ color:var(--ink-500); }
  span.flatpickr-weekday{ color:var(--ink-500); }
  .flatpickr-day{ color:var(--ink-700); border-radius:8px; }
  .flatpickr-day:hover{ background:var(--blue-50); border-color:var(--blue-50); }
  .flatpickr-day.today{ border-color:var(--blue-500); }
  .flatpickr-day.selected, .flatpickr-day.selected:hover, .flatpickr-day.week.selected{ background:var(--blue-600); border-color:var(--blue-600); color:#fff; }
  .flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover, .flatpickr-day.nextMonthDay, .flatpickr-day.prevMonthDay{ color:var(--ink-300); }
  .flatpickr-months .flatpickr-prev-month:hover svg, .flatpickr-months .flatpickr-next-month:hover svg{ fill:var(--blue-600); }
  .flatpickr-time input:hover, .flatpickr-time input:focus{ background:var(--blue-50); }
  .flatpickr-monthDropdown-months::-webkit-scrollbar, .flatpickr-monthDropdown-months::-moz-scrollbar{ width:6px; }
  .photo-backdrop{ display:none; position:fixed; inset:0; z-index:999; background:rgba(8,11,18,.88);
    align-items:center; justify-content:center; padding:24px; }
  .photo-backdrop.open{ display:flex; }
  .photo-backdrop img{ max-width:min(760px, 92vw); max-height:86vh; border-radius:14px; object-fit:contain;
    box-shadow:0 24px 80px rgba(0,0,0,.55); background:#fff; }
  .photo-backdrop .pb-close{ position:absolute; top:18px; right:22px; width:40px; height:40px; border-radius:50%;
    display:flex; align-items:center; justify-content:center; cursor:pointer; color:#fff;
    background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.25); }
  .photo-backdrop .pb-close:hover{ background:rgba(255,255,255,.28); }

  .section-title{ font-family:var(--font-head); font-size:11px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:var(--blue-800); }
  .foot{ font-size:11.5px; color:var(--ink-300); text-align:center; padding:20px 0 26px; }

  /* ============================================================
     ENTRANCE — page transition + staggered content (halus, CSS only)
     ============================================================ */
  @keyframes pageIn{ from{ opacity:0; transform:translateY(4px); } to{ opacity:1; transform:translateY(0); } }
  @keyframes cardIn{ from{ opacity:0; transform:translateY(8px); } to{ opacity:1; transform:translateY(0); } }
  .main-inner{ animation:pageIn .22s ease; }
  .stagger > *{ animation:cardIn .42s cubic-bezier(.22,1,.36,1) backwards; }
  .stagger > *:nth-child(1){ animation-delay:.04s; }
  .stagger > *:nth-child(2){ animation-delay:.09s; }
  .stagger > *:nth-child(3){ animation-delay:.14s; }
  .stagger > *:nth-child(4){ animation-delay:.19s; }
  .stagger > *:nth-child(5){ animation-delay:.24s; }
  .stagger > *:nth-child(n+6){ animation-delay:.29s; }
  @media (prefers-reduced-motion:reduce){
    .main-inner, .stagger > *, html.brand-anim .brand-plate, html.brand-anim .brand-text,
    .skeleton::after, .modal-backdrop.open, .modal, .donut, .dot-live, .progress > div::after{ animation:none !important; }
    .stat-card, .stat-card:hover, .navlink, .icon-btn, .btn, .hrow, .min-act .act-ic, .min-act .chev{ transition:none !important; }
    *{ scroll-behavior:auto !important; }
  }

  .progress{ height:6px; border-radius:999px; background:var(--line-soft); overflow:hidden; }
  .progress > div{ height:100%; border-radius:999px; transition:width .4s ease; }
  .field-group{ font-size:10.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
                color:var(--ink-400); margin:20px 0 12px; }
  .field-group:first-of-type{ margin-top:2px; }
/* Info summary (read-only, enterprise) - label + value, bukan kotak input */
  .ik-head{ display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
  .ik-title{ font-family:var(--font-head); font-size:16px; font-weight:700; color:var(--ink-900); line-height:1.3; }
  .ik-sub{ font-size:13px; color:#64748B; margin-top:4px; }
  .ik{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px 24px; align-items:start; }
  .ik-item{ min-width:0; }
  .ik .lg-2{ grid-column:1 / -1; }
  #ajukan-perubahan{ scroll-margin-top:84px; }
  .ik-label{ display:block; font-size:12px; font-weight:600; color:#64748B; margin-bottom:6px; }
  .ik-value{ font-size:14px; font-weight:500; color:#172033; line-height:1.5; word-break:break-word; }
  .ik-dim{ color:#98A2B3; font-weight:500; }
  .ik-badge{ display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; font-size:12px;
             font-weight:600; background:#ECFDF3; border:1px solid #ABEFC6; color:#067647; white-space:nowrap; }
  .ik-badge .ik-dot{ width:6px; height:6px; border-radius:50%; background:#039855; flex:none; }
  .ik-badge.is-empty{ background:#F8FAFC; border-color:#E5EAF0; color:#98A2B3; font-weight:500; }
  .ik-badge.is-empty .ik-dot{ background:#CBD5E1; }
  .ik-note{ display:flex; align-items:center; gap:6px; margin-top:20px; font-size:12px; color:#64748B; }
  .ik-note svg{ width:13px; height:13px; flex:none; color:#93A4B8; }

  /* --- Profile page (ESS self-service) — server CSS, tanpa utility framework --- */
  .card-pad{ padding:24px; }
  .card-pad-sm{ padding:16px; }
  .cards-gap{ margin-bottom:20px; }
  .sec-desc{ font-size:12px; color:var(--ink-400); margin-top:3px; }
  .prof-sum{ display:flex; flex-wrap:wrap; align-items:center; gap:24px; width:100%; }
  .prof-sum-left{ display:flex; align-items:center; gap:20px; flex:1 1 420px; min-width:0; }
  .prof-sum-meta{ min-width:0; }
  .prof-name{ font-family:var(--font-head); font-size:18px; font-weight:700; color:var(--ink-900); line-height:1.3;
              overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .prof-role{ font-size:13px; color:var(--ink-500); margin-top:2px; }
  .prof-nip{ display:flex; align-items:center; flex-wrap:wrap; gap:6px; font-size:13px; color:var(--ink-500); margin-top:10px; }
  .prof-nip b{ color:var(--ink-700); font-weight:600; }
  .prof-dot{ color:var(--ink-200); }
  .prof-sum-comp{ flex:0 1 300px; min-width:220px; margin-left:auto; }
  .prof-status{ margin-top:8px; }
  .prof-comp-head{ display:flex; align-items:center; justify-content:space-between; font-size:12px; margin-bottom:6px; }
  .prof-comp-head span{ color:var(--ink-500); }
  .prof-comp-head b{ color:var(--ink-900); font-weight:700; }
  .prof-comp-hint{ font-size:11.5px; color:var(--ink-400); margin-top:8px; }
  .profile-avatar{ width:80px; height:80px; min-width:80px; min-height:80px; max-width:80px; max-height:80px;
                   border-radius:50%; overflow:hidden; flex:none; position:relative; background-color:var(--blue-600);
                   box-shadow:var(--shadow-sm); border:2px solid #fff; }
  .profile-avatar img{ width:100%; height:100%; object-fit:cover; display:block; }
  .profile-avatar .pa-inits{ position:absolute; inset:0; display:none; align-items:center; justify-content:center;
                             color:#fff; font-family:var(--font-head); font-weight:700; font-size:26px; }
  .profile-avatar-wrap{ position:relative; flex:none; }
  .profile-avatar-wrap .avatar-edit{ position:absolute; right:-4px; bottom:-4px; width:28px; height:28px; border-radius:50%;
    display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:3;
    border:2px solid #fff; background:var(--blue-600); color:#fff; box-shadow:var(--shadow-sm); }
  .profile-avatar-wrap .avatar-edit:hover{ background:var(--blue-700); }
  .profile-avatar-wrap .avatar-delete{ position:absolute; left:-4px; bottom:-4px; width:28px; height:28px; border-radius:50%;
    display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:3;
    border:2px solid #fff; background:rgba(185,28,28,.85); color:#fff; box-shadow:var(--shadow-sm); }
  .profile-avatar-wrap .avatar-delete:hover{ background:var(--red-600); }
  .fg{ display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:20px; align-items:start; }
  .fg > .lg-2{ grid-column:1 / -1; }
  .pend-alert{ display:flex; align-items:center; gap:14px; }
  .pend-alert .ic{ flex:none; width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; }
  .pend-alert .txt{ flex:1; min-width:0; }
  .pend-t{ font-size:13.5px; font-weight:600; color:#92400E; }
  .pend-s{ font-size:12.5px; color:#A16207; margin-top:2px; }
  .btn-row{ display:flex; flex-wrap:wrap; align-items:center; gap:12px; margin-top:24px; padding-top:20px;
            border-top:1px solid var(--line-soft); }
  @media (min-width:721px) and (max-width:1024px){
    .profile-avatar{ width:72px; height:72px; min-width:72px; min-height:72px; max-width:72px; max-height:72px; }
  }
  @media (max-width:720px){
    .profile-avatar{ width:64px; height:64px; min-width:64px; min-height:64px; max-width:64px; max-height:64px; }
    .profile-avatar .pa-inits{ font-size:22px; }
    .fg{ grid-template-columns:1fr; }
    .ik{ grid-template-columns:1fr; }
    .prof-sum-comp{ flex-basis:100%; margin-left:0; }
  }

  .chip{ display:inline-flex; align-items:center; gap:6px; border:1px solid var(--line); background:#fff; border-radius:9px;
         padding:6px 12px; font-size:12.5px; font-weight:500; color:var(--ink-700); white-space:nowrap; transition:.14s; }
  .chip:hover{ border-color:var(--blue-600); color:var(--blue-600); }
  .chip.active{ background:var(--blue-50); border-color:var(--blue-600); color:var(--blue-600); font-weight:600; }

  /* Modal */
  .modal-backdrop{ position:fixed; inset:0; z-index:var(--z-modal); background:rgba(11,18,32,.5); display:none; align-items:center; justify-content:center; padding:16px; }
  .modal-backdrop.open{ display:flex; animation:backdropIn .16s ease; }
  .modal{ width:100%; max-width:440px; background:#fff; border-radius:16px; box-shadow:var(--shadow-md); animation:modalPop .22s cubic-bezier(.22,1,.36,1); }
  @keyframes backdropIn{ from{ opacity:0; } to{ opacity:1; } }
  @keyframes modalPop{ from{ opacity:0; transform:translateY(10px) scale(.96); } to{ opacity:1; transform:translateY(0) scale(1); } }

  /* Print */
  @media print{
    .side, .topbar, footer.foot, .no-print, .page-head{ display:none !important; }
    .main-wrap{ margin-left:0 !important; }
    .main-content, .main-inner{ padding:0 !important; margin:0 !important; max-width:100% !important; }
    .card{ box-shadow:none !important; border-color:#ddd; }
  }

  /* Pagination reskin (markup default Laravel) */
  .pagination{ display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
  .pagination .page-item .page-link{ display:inline-flex; align-items:center; justify-content:center; min-width:34px; height:34px;
    padding:0 10px; border:1px solid var(--line); border-radius:9px; background:#fff; color:var(--ink-700); font-size:13px; font-weight:500; }
  .pagination .page-item .page-link:hover{ border-color:var(--blue-600); color:var(--blue-600); }
  .pagination .page-item.active .page-link{ background:var(--blue-600); border-color:var(--blue-600); color:#fff; }
  .pagination .page-item.disabled .page-link{ color:var(--ink-300); background:var(--line-soft); }

  /* Timeline */
  .timeline{ position:relative; padding-left:26px; }
  .timeline::before{ content:''; position:absolute; left:7px; top:6px; bottom:6px; width:2px; background:var(--line); }
  .tl-item{ position:relative; padding:0 0 22px 0; }
  .tl-item:last-child{ padding-bottom:0; }
  .tl-dot{ position:absolute; left:-21px; top:3px; width:12px; height:12px; border-radius:50%;
           background:var(--blue-600); box-shadow:0 0 0 3px var(--blue-50); }
  .tl-year{ font-size:11px; font-weight:700; letter-spacing:.05em; color:var(--ink-300); text-transform:uppercase; }

  .skeleton{ position:relative; overflow:hidden; background:var(--line-soft); border-radius:8px; }
  .skeleton::after{ content:''; position:absolute; inset:0; transform:translateX(-100%);
    background:linear-gradient(90deg, transparent, rgba(255,255,255,.6), transparent); animation:sh 1.3s infinite; }
  @keyframes sh{ 100%{ transform:translateX(100%); } }

  /* Toast (flash auto-dismiss) */
  .toast-wrap{ position:fixed; top:76px; right:20px; z-index:200; display:flex; flex-direction:column; gap:10px; width:min(360px,calc(100vw - 40px)); }
  .toast{ display:flex; align-items:flex-start; gap:10px; padding:12px 14px; background:#fff; border:1px solid var(--line);
          border-left:4px solid var(--blue-600); border-radius:12px; box-shadow:var(--shadow-md); font-size:13px; color:var(--ink-700); animation:tin .2s ease; }
  .toast-success{ border-left-color:var(--green-600); } .toast-success .t-ic{ color:var(--green-600); }
  .toast-error{ border-left-color:var(--red-600); } .toast-error .t-ic{ color:var(--red-600); }
  .toast-warning{ border-left-color:var(--amber-600); } .toast-warning .t-ic{ color:var(--amber-600); }
  .toast .t-ic{ width:18px; height:18px; flex:none; }
  .toast .t-close{ margin-left:auto; color:var(--ink-300); cursor:pointer; }
  @keyframes tin{ from{ transform:translateY(-8px); opacity:0; } to{ transform:translateY(0); opacity:1; } }

  /* Mobile drawer */
  .mobile-backdrop{ position:fixed; inset:0; z-index:var(--z-backdrop); background:rgba(16,24,40,.45); display:none; }

  @media (max-width:1023px){
    .side{ width:280px; transform:translateX(-100%); }
    .app-shell.mobile-open .side{ transform:translateX(0); box-shadow:var(--shadow-md); }
    .app-shell.mobile-open .mobile-backdrop{ display:block; }
    .main-wrap{ margin-left:0 !important; }
    .topbar-search input{ width:min(220px,32vw); }
    .crumb .cur{ max-width:200px; overflow:hidden; text-overflow:ellipsis; }
  }
  @media (max-width:640px){
    :root{ --content-pad:16px; }
    .t-h1{ font-size:22px; }
    .main-content{ padding-top:22px; }
    .topbar{ padding:0 14px; gap:8px; }
  }
</style>
<script>try{if(!sessionStorage.getItem('rskk_brand_seen')){document.documentElement.classList.add('brand-anim');sessionStorage.setItem('rskk_brand_seen','1');}}catch(e){document.documentElement.classList.add('brand-anim');}</script>
</head>
<body>
@php
  $appName = App\Models\Settings::get('app_name', 'SIMPEG RSKK');
  $appTagline = App\Models\Settings::get('app_tagline', 'Sistem Informasi Kepegawaian');
  $rsName = App\Models\Settings::get('rs_name', 'RSUD Kesehatan Kerja');
  $roleLabel = ['super_admin' => 'Super Admin', 'admin' => 'Admin', 'user' => 'Pegawai'][auth()->user()->role] ?? ucwords(str_replace('_', ' ', auth()->user()->role));
  $unreadNotif = auth()->user()->appNotifications()->unread()->count();
  $userName = auth()->user()->name;
  $userEmp = auth()->user()->employee;
  $userAvatar = $userEmp?->foto_path ? asset('storage/' . $userEmp->foto_path) : 'images/default-avatar.png';
@endphp

<div class="app-shell" id="app-shell">
  {{-- ================= SIDEBAR ================= --}}
  <aside id="sidebar" class="side">
    {{-- Brand --}}
    <div class="side-head">
      <div class="brand-plate">
        <img src="{{ asset('assets/images/logo-rskk.png') }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
          alt="Logo RSKK">
        <span class="ph">R</span>
      </div>
      <div class="brand-text">
        <div class="brand-title">{{ $appName }}</div>
        <div class="brand-sub">{{ $appTagline }}</div>
      </div>
    </div>

    {{-- Nav --}}
    <nav class="side-nav">
      <div class="navgroup">Dashboard</div>
      <a class="navlink @yield('nav-dashboard')" href="{{ route('dashboard') }}" title="Dashboard">
        <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-8 9 8M5 10v10h5v-6h4v6h5V10"/></svg>
        <span class="navtext">Dashboard</span>
      </a>
      <a class="navlink @yield('nav-notifications')" href="{{ route('notifications.index') }}" title="Notifikasi">
        <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 01-6 0"/></svg>
        <span class="navtext">Notifikasi</span>
        @if($unreadNotif > 0)<span class="nav-badge">{{ $unreadNotif }}</span>@endif
      </a>

      @if(auth()->user()->role !== 'user')
        <div class="navgroup">Kepegawaian</div>
        <a class="navlink @yield('nav-employees')" href="{{ route('employees.index') }}" title="Data Pegawai">
          <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.9M9 20H4v-2a4 4 0 013-3.9m6-1.6a4 4 0 10-4-4m8 0a4 4 0 11-4-4M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span class="navtext">Data Pegawai</span>
        </a>
        <a class="navlink @yield('nav-documents')" href="{{ route('documents.index') }}" title="Dokumen Digital">
          <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2zM15 3v6h6"/></svg>
          <span class="navtext">Dokumen Digital</span>
        </a>
        <a class="navlink @yield('nav-approvals')" href="{{ route('approvals.index') }}" title="Approval">
          <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span class="navtext">Approval</span>
        </a>

        <div class="navgroup">Administrasi</div>
        {{-- Manajemen Akun �?" HANYA Super Admin --}}
        @if(auth()->user()->role === 'super_admin')
        <a class="navlink @yield('nav-accounts')" href="{{ route('admin.accounts.index') }}" title="Manajemen Akun">
          <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.4-1.9M13 20H3v-2a4 4 0 016-3.4m6-1.6a3 3 0 10-3-3M19 7v4m-2-2h4"/></svg>
          <span class="navtext">Manajemen Akun</span>
        </a>
        @endif
        <a class="navlink @yield('nav-master')" href="{{ route('master.index', 'units') }}" title="Master Data">
          <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M10.3 4.3l1.4-2.4a1 1 0 011.7 0l1.4 2.4a1 1 0 001 .5l2.7-.3a1 1 0 011 1v2.7a1 1 0 00.4.8l2 1.7a1 1 0 010 1.6l-2 1.7a1 1 0 00-.4.8v2.7a1 1 0 01-1 1l-2.7-.3a1 1 0 00-1 .5l-1.4 2.4a1 1 0 01-1.7 0l-1.4-2.4a1 1 0 00-1-.5l-2.7.3a1 1 0 01-1-1v-2.7a1 1 0 00-.4-.8l-2-1.7a1 1 0 010-1.6l2-1.7a1 1 0 00.4-.8V4.1a1 1 0 011-1l2.7.3a1 1 0 001-.5z"/></svg>
          <span class="navtext">Master Data</span>
        </a>
        <a class="navlink @yield('nav-reports')" href="{{ route('reports.index') }}" title="Laporan">
          <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6a2 2 0 011.5.7l3.6 3.6a2 2 0 01.5 1.4V15a2 2 0 01-2 2z"/></svg>
          <span class="navtext">Laporan</span>
        </a>
        <a class="navlink @yield('nav-audit')" href="{{ route('audit.index') }}" title="Audit Log">
          <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
          <span class="navtext">Audit Log</span>
        </a>

        <div class="navgroup">Akun</div>
        <a class="navlink @yield('nav-profile')" href="{{ route('profile.edit') }}" title="Profil Saya">
          <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          <span class="navtext">Profil Saya</span>
        </a>
        <a class="navlink @yield('nav-password')" href="{{ route('password.edit') }}" title="Pengaturan">
          <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          <span class="navtext">Pengaturan</span>
        </a>
        @if(auth()->user()->role === 'super_admin')
          <a class="navlink @yield('nav-settings')" href="{{ route('settings.edit') }}" title="Pengaturan Sistem">
            <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M10.3 4.3l1.4-2.4a1 1 0 011.7 0l1.4 2.4a1 1 0 001 .5l2.7-.3a1 1 0 011 1v2.7a1 1 0 00.4.8l2 1.7a1 1 0 010 1.6l-2 1.7a1 1 0 00-.4.8v2.7a1 1 0 01-1 1l-2.7-.3a1 1 0 00-1 .5l-1.4 2.4a1 1 0 01-1.7 0l-1.4-2.4a1 1 0 00-1-.5l-2.7.3a1 1 0 01-1-1v-2.7a1 1 0 00-.4-.8l-2-1.7a1 1 0 010-1.6l2-1.7a1 1 0 00.4-.8V4.1a1 1 0 011-1l2.7.3a1 1 0 001-.5z"/></svg>
            <span class="navtext">Pengaturan Sistem</span>
          </a>
        @endif
      @else
        <div class="navgroup">Kepegawaian</div>
        <a class="navlink @yield('nav-profile')" href="{{ route('profile.edit') }}" title="Profil Saya">
          <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          <span class="navtext">Profil Saya</span>
        </a>
        <a class="navlink @yield('nav-documents')" href="{{ route('documents.index') }}" title="Dokumen Saya">
          <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2zM15 3v6h6"/></svg>
          <span class="navtext">Dokumen Saya</span>
        </a>

        <div class="navgroup">Akun</div>
        <a class="navlink @yield('nav-password')" href="{{ route('password.edit') }}" title="Pengaturan">
          <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          <span class="navtext">Pengaturan</span>
        </a>
      @endif
    </nav>

    {{-- Footer user --}}
    <div class="side-foot">
      <a class="side-user" href="{{ route('profile.edit') }}" title="Profil Saya">
        <img src="{{ asset($userAvatar) }}" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($userName) }}&background=2563EB&color=fff&size=64'" class="avatar flex-none" style="width:36px;height:36px">
        <div class="user-text leading-tight min-w-0 flex-1">
          <div class="text-[13px] font-semibold truncate" style="color:var(--ink-900)">{{ $userName }}</div>
          <div class="text-[11px]" style="color:var(--ink-400)">{{ $roleLabel }}</div>
        </div>
        <svg class="user-chevron" style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
      </a>
      <button class="side-logout" onclick="openModal('logout-modal'); return false;" title="Logout">
        <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        <span class="navtext">Logout</span>
      </button>
    </div>
  </aside>

  {{-- Backdrop mobile --}}
  <div class="mobile-backdrop" onclick="toggleSidebar(false)"></div>

  <div class="main-wrap">
    {{-- ================= TOPBAR ================= --}}
    <header class="topbar">
      <button class="icon-btn flex-none" onclick="toggleSidebar()" title="Menu">
        <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>

      <div class="flex items-center min-w-0" style="gap:clamp(4px,1vw,10px)">
        <span class="crumb" aria-label="Lokasi halaman">
          <span>@yield('crumb', 'Utama')</span>
          <span class="sep">/</span>
          <span class="cur">@yield('title', 'Dashboard')</span>
        </span>
      </div>

      @if(auth()->user()->role !== 'user')
      <div class="flex-1 flex justify-center min-w-0 px-2">
        <form method="GET" action="{{ route('employees.index') }}" class="topbar-search hidden md:block" autocomplete="off">
          <input type="text" name="q" id="gs" value="{{ request('q') }}" placeholder="Cari nama, NIP, jabatan, atau unit kerja..." aria-label="Pencarian global">
          <svg class="si" style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <div class="gs-res" id="gs-res"></div>
        </form>
      </div>
      @endif

      <div class="flex flex-none items-center" style="gap:8px">
        @php $recentNotifs = auth()->user()->appNotifications()->latest()->limit(5)->get(); @endphp
        <div class="dropdown" id="dd-notif">
          <button class="icon-btn relative" onclick="toggleDropdown('dd-notif')" title="Notifikasi" aria-label="Notifikasi">
            <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 01-6 0"/></svg>
            @if($unreadNotif > 0)
              <span class="absolute flex items-center justify-center text-[9px] font-bold text-white" style="top:1px;right:1px;min-width:16px;height:16px;border-radius:100px;background:var(--red-600);border:2px solid #fff">{{ $unreadNotif }}</span>
            @endif
          </button>
          <div class="dropdown-menu">
            <div class="flex items-center justify-between px-4 py-3 border-b" style="border-color:var(--line-soft)">
              <span class="menu-title">Notifikasi</span>
              <a href="{{ route('notifications.index') }}" class="text-[11.5px] font-semibold" style="color:var(--blue-600)">Lihat Semua</a>
            </div>
            <div class="max-h-72 overflow-y-auto">
              @forelse($recentNotifs as $n)
                <a href="{{ route('notifications.read', $n) }}" class="menu-item items-start">
                  <span class="w-2 h-2 rounded-full mt-1.5 flex-none" style="background:{{ $n->read_at ? 'var(--ink-200)' : 'var(--blue-600)' }}"></span>
                  <span class="min-w-0">
                    <span class="block font-semibold text-[12.5px]">{{ $n->title }}</span>
                    @if($n->body)<span class="block text-[11.5px] truncate" style="color:var(--ink-500)">{{ $n->body }}</span>@endif
                    <span class="block text-[10.5px] mt-0.5" style="color:var(--ink-300)">{{ $n->created_at->diffForHumans() }}</span>
                  </span>
                </a>
              @empty
                <div class="px-4 py-6 text-center text-[12px]" style="color:var(--ink-500)">Belum ada notifikasi.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </header>

    {{-- ================= CONTENT ================= --}}
    <main class="main-content">
      <div class="main-inner">
        @if(session('success'))
          <div class="alert alert-success mb-4">
            <svg class="w-4 h-4 flex-none mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
          </div>
        @endif
        @if(session('error'))
          <div class="alert alert-danger mb-4">
            <svg class="w-4 h-4 flex-none mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
          </div>
        @endif
        @yield('content')
      </div>
    </main>
    <footer class="foot">© {{ date('Y') }} {{ $appName }} — {{ $rsName }} · {{ $appTagline }}</footer>
  </div>
</div>

{{-- Logout modal --}}
<div class="modal-backdrop" id="logout-modal" onclick="if(event.target===this) closeModal('logout-modal')">
  <div class="modal p-6">
    <div class="flex items-center gap-3 mb-3">
      <span class="w-10 h-10 rounded-full flex items-center justify-center flex-none" style="background:var(--red-50); color:var(--red-600)">
        <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      </span>
      <h3 class="text-[15px] font-bold" style="color:var(--ink-900)">Keluar dari {{ $appName }}?</h3>
    </div>
    <p class="text-[13px] mb-5" style="color:var(--ink-500)">Anda akan keluar dari akun dan kembali ke halaman login.</p>
    <div class="flex justify-end gap-2">
      <button class="btn btn-outline px-4 py-2" onclick="closeModal('logout-modal')">Batal</button>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-danger px-4 py-2">Ya, Keluar</button>
      </form>
    </div>
  </div>
</div>

{{-- Foto profil viewer (lightbox) --}}
<div class="photo-backdrop" id="photo-backdrop" onclick="if(event.target===this) closePhotoViewer()">
  <button type="button" class="pb-close" onclick="closePhotoViewer()" aria-label="Tutup">
    <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
  </button>
  <img id="photo-backdrop-img" src="" alt="Foto Profil">
</div>

<script>
  const shell = document.getElementById('app-shell');
  const isMobile = () => window.innerWidth <= 1023;
  function toggleSidebar(force){
    const m = isMobile();
    if(m){
      const open = force !== undefined ? force : !shell.classList.contains('mobile-open');
      shell.classList.toggle('mobile-open', open);
    } else {
      shell.classList.toggle('collapsed');
    }
  }
  function toggleDropdown(id){
    document.querySelectorAll('.dropdown.open').forEach(d => { if(d.id !== id) d.classList.remove('open'); });
    const el = document.getElementById(id);
    if(el) el.classList.toggle('open');
  }
  function openModal(id){ document.getElementById(id).classList.add('open'); }
  function closeModal(id){ document.getElementById(id).classList.remove('open'); }
  function openPhotoViewer(src){
    const img = document.getElementById('photo-backdrop-img');
    if(img && src) img.src = src;
    document.getElementById('photo-backdrop').classList.add('open');
  }
  function closePhotoViewer(){ document.getElementById('photo-backdrop').classList.remove('open'); }
  document.addEventListener('click', (e) => {
    if(!e.target.closest('.dropdown')){
      document.querySelectorAll('.dropdown.open').forEach(d => d.classList.remove('open'));
    }
  });
  document.addEventListener('keydown', (e) => { if(e.key === 'Escape'){ closeModal('logout-modal'); closePhotoViewer(); } });

  /* Flash alert → auto-dismiss toast (non-intrusive) */
  (function(){
    var wrap = document.createElement('div');
    wrap.className = 'toast-wrap';
    wrap.id = 'toast-wrap';
    document.body.appendChild(wrap);
    window.toast = function(message, type){
      type = type || 'success';
      var t = document.createElement('div');
      t.className = 'toast toast-' + type;
      var icons = { success:'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', error:'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', warning:'M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z' };
      t.innerHTML = '<svg class="t-ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="' + (icons[type] || icons.success) + '"/></svg>' +
        '<span style="flex:1">{message}</span>'.replace('{message}', (message||'').replace(/&/g,'&amp;').replace(/</g,'&lt;')) +
        '<button class="t-close" onclick="this.parentNode.remove()" style="border:none;background:none;cursor:pointer" aria-label="Tutup">✕</button>';
      wrap.appendChild(t);
      setTimeout(function(){ t.style.transition = 'opacity .3s, transform .3s'; t.style.opacity = '0'; t.style.transform = 'translateY(-6px)'; setTimeout(function(){ t.remove(); }, 320); }, 4200);
    };
    /* move existing inline flash alerts into toasts for clean page header */
    setTimeout(function(){
      document.querySelectorAll('.main-content > .alert').forEach(function(a){
        var text = a.innerText.trim();
        if(!text) return;
        window.toast(text, a.classList.contains('alert-success') ? 'success' : (a.classList.contains('alert-danger') ? 'error' : 'warning'));
        a.remove();
      });
    }, 60);
  })();

  /* Loading button driver (reusable) */
  (function(){
    document.addEventListener('submit', function(e){
      var form = e.target;
      if(!form || !form.querySelector) return;
      if(!form.checkValidity()) return;
      form.querySelectorAll('[data-loading-btn]').forEach(function(btn){
        btn.disabled = true;
        var ic = btn.querySelector('[data-lb-ic]');
        if(ic){ ic.classList.remove('lb-hidden'); ic.classList.add('lb-spin'); }
        var tx = btn.querySelector('[data-lb-tx]');
        if(tx) tx.textContent = btn.getAttribute('data-loading-text') || 'Menyimpan...';
      });
    });
  })();

  (function(){
    var inp = document.getElementById('gs');
    var res = document.getElementById('gs-res');
    if(!inp || !res) return;
    var t = null, ctrl = null;
    inp.addEventListener('input', function(){
      clearTimeout(t);
      var q = inp.value.trim();
      if(q.length < 2){ res.innerHTML=''; res.classList.remove('has'); return; }
      t = setTimeout(function(){
        if(ctrl) ctrl.abort();
        ctrl = new AbortController();
        fetch('{{ route("employees.search") }}?q='+encodeURIComponent(q), {signal:ctrl.signal})
          .then(function(r){ return r.json(); })
          .then(function(rows){
            if(!rows.length){ res.innerHTML='<div class="gs-empty">Tidak ditemukan</div>'; res.classList.add('has'); return; }
            res.innerHTML = rows.map(function(e){
              var ini = (e.nama||'').split(' ').map(function(w){return w.charAt(0)}).slice(0,2).join('').toUpperCase();
              return '<a href="'+e.url+'" class="gs-res-item">'
                +'<div class="gs-avatar">'+ini+'</div>'
                +'<div class="gs-meta"><div class="gs-name">'+(e.nama||'-')+'</div>'
                +'<div class="gs-sub">'+(e.jabatan||'')+(e.unit?' · '+e.unit:'')+'</div></div></a>';
            }).join('');
            res.classList.add('has');
          }).catch(function(){});
      }, 260);
    });
    document.addEventListener('click', function(e){ if(!e.target.closest('.topbar-search')) res.classList.remove('has'); });
    inp.form.addEventListener('submit', function(){ res.classList.remove('has'); });
  })();

  /* Date picker: Flatpickr + dropdown bulan/tahun utk semua input tanggal.
     Fallback ke datepicker native bila CDN gagal dimuat. */
  (function(){
    function initPicker(el){
      if(el.getAttribute('data-fp')) return;
      el.setAttribute('data-fp', '1');
      try {
        window.flatpickr(el, {
          dateFormat: 'Y-m-d',
          monthSelectorType: 'dropdown',
          showMonths: 1,
          position: 'auto',
          static: false,
          disableMobile: true,
          allowInput: false,
          defaultDate: el.value ? new Date(el.value + 'T00:00:00') : null,
          onChange: function(selected, dateStr){ el.value = dateStr || ''; }
        });
      } catch(err){ el.style.visibility = 'visible'; }
    }
    var cinp = document.querySelectorAll('input[type="date"]');
    cinp.forEach(initPicker);
    new MutationObserver(function(muts){
      muts.forEach(function(m){
        m.addedNodes.forEach(function(n){
          if(!n.querySelectorAll) return;
          n.querySelectorAll('input[type="date"]').forEach(initPicker);
        });
      });
    }).observe(document.body, { childList:true, subtree:true });
  })();
</script>
@stack('scripts')
</body>
</html>