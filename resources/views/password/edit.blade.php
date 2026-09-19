@extends('layouts.app')
@section('title', 'Ubah Password')
@section('crumb', 'Pengaturan')
@section('nav-password', 'active')

@section('content')
<div class="mx-auto w-full" style="max-width:1180px">

  {{-- Page head --}}
  <div class="page-head" style="margin-bottom:24px">
    <div>
      <h1 class="page-title">Ubah Password</h1>
      <p class="page-desc">Perbarui kata sandi akun secara berkala untuk menjaga keamanan akun.</p>
    </div>
  </div>

  @if($errors->any())
    <div class="mb-5">
      <x-alert type="error" title="Password gagal diperbarui">Silakan periksa kembali data yang Anda isi, lalu coba lagi.</x-alert>
    </div>
  @endif

  <div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-6 items-start">

    {{-- LEFT: form card --}}
    <form method="POST" action="{{ route('password.update') }}" class="card overflow-hidden" id="form-password">
      @csrf
      @method('PUT')

      <div class="px-7 pt-6 pb-5 border-b" style="border-color:var(--line-soft)">
        <div class="settings-card-head">
          <span class="shield">
            <svg style="width:20px;height:20px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.6-3.6A2 2 0 0019 5H5a2 2 0 00-2 2v8a2 2 0 002 2h11.7l2.7 2.7v-12zM12 8v3"/></svg>
          </span>
          <div>
            <div class="settings-card-title">Keamanan Akun</div>
            <div class="settings-card-sub">Ubah kata sandi secara berkala untuk menjaga keamanan akun SIMPEG RSKK.</div>
          </div>
        </div>
      </div>

      <div class="px-7 py-7" style="display:grid;gap:24px">

        {{-- Current --}}
        <div>
          <label class="flabel" for="current_password">Password Saat Ini <span class="req">*</span></label>
          <div class="pw-input">
            <input type="password" name="current_password" id="current_password" class="input" style="height:44px" required
                   autocomplete="current-password" placeholder="Masukkan password saat ini" data-pw>
            <button type="button" class="pw-eye" data-eye aria-label="Tampilkan password" tabindex="-1">
              <svg class="eye-open" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.06 12.35a1 1 0 010-.7C3.8 8.25 7.5 5 12 5s8.2 3.25 9.94 6.65a1 1 0 010 .7C20.2 15.75 16.5 19 12 19s-8.2-3.25-9.94-6.35zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <svg class="eye-off" style="width:18px;height:18px;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 5.1A9.6 9.6 0 0112 5c4.5 0 8.2 3.25 9.94 6.65a1 1 0 010 .7 10.8 10.8 0 01-2.6 3.4M6 6a10.7 10.7 0 00-3.94 5.65 1 1 0 000 .7C3.8 15.75 7.5 19 12 19a9.3 9.3 0 003.6-.7"/></svg>
            </button>
          </div>
          <x-form-error field="current_password" />
        </div>

        {{-- New --}}
        <div>
          <label class="flabel" for="password">Password Baru <span class="req">*</span></label>
          <div class="pw-input">
            <input type="password" name="password" id="password" class="input" style="height:44px" required minlength="8"
                   autocomplete="new-password" placeholder="Masukkan password baru" data-pw data-strength>
            <button type="button" class="pw-eye" data-eye aria-label="Tampilkan password" tabindex="-1">
              <svg class="eye-open" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.06 12.35a1 1 0 010-.7C3.8 8.25 7.5 5 12 5s8.2 3.25 9.94 6.65a1 1 0 010 .7C20.2 15.75 16.5 19 12 19s-8.2-3.25-9.94-6.35zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <svg class="eye-off" style="width:18px;height:18px;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 5.1A9.6 9.6 0 0112 5c4.5 0 8.2 3.25 9.94 6.65a1 1 0 010 .7 10.8 10.8 0 01-2.6 3.4M6 6a10.7 10.7 0 00-3.94 5.65 1 1 0 000 .7C3.8 15.75 7.5 19 12 19a9.3 9.3 0 003.6-.7"/></svg>
            </button>
          </div>

          <div class="fs-meter" style="display:none">
            <div class="flex items-center justify-between mb-1.5">
              <span class="fs-label">Kekuatan Password</span>
              <span class="fs-value" id="pw-strength-txt"></span>
            </div>
            <div class="strength-seg" id="pw-seg" aria-live="polite">
              <i></i><i></i><i></i><i></i>
            </div>
            <div class="field-hint">Minimal 8 karakter.</div>
          </div>
          <x-form-error field="password" />
        </div>

        {{-- Confirm --}}
        <div>
          <label class="flabel" for="password_confirmation">Ulangi Password Baru <span class="req">*</span></label>
          <div class="pw-input">
            <input type="password" name="password_confirmation" id="password_confirmation" class="input" style="height:44px" required minlength="8"
                   autocomplete="new-password" placeholder="Ulangi password baru" data-pw data-confirm>
            <button type="button" class="pw-eye" data-eye aria-label="Tampilkan password" tabindex="-1">
              <svg class="eye-open" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.06 12.35a1 1 0 010-.7C3.8 8.25 7.5 5 12 5s8.2 3.25 9.94 6.65a1 1 0 010 .7C20.2 15.75 16.5 19 12 19s-8.2-3.25-9.94-6.35zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <svg class="eye-off" style="width:18px;height:18px;display:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 5.1A9.6 9.6 0 0112 5c4.5 0 8.2 3.25 9.94 6.65a1 1 0 010 .7 10.8 10.8 0 01-2.6 3.4M6 6a10.7 10.7 0 00-3.94 5.65 1 1 0 000 .7C3.8 15.75 7.5 19 12 19a9.3 9.3 0 003.6-.7"/></svg>
            </button>
          </div>
          <span class="pw-match" id="pw-match"><svg style="width:13px;height:13px;flex:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span id="pw-match-txt"></span></span>
          <x-form-error field="password_confirmation" />
        </div>
      </div>

      <div class="px-7 py-5 border-t flex flex-wrap items-center justify-end gap-3" style="border-color:var(--line-soft); background:#FAFBFD">
        <button type="button" class="btn btn-outline" style="height:44px;padding:0 22px" onclick="window.history.back()">Batal</button>
        <x-loading-button label="Simpan Password Baru" loading="Menyimpan..." class="btn btn-primary" style="height:44px;padding:0 24px"/>
      </div>
    </form>

    {{-- RIGHT: security panel --}}
    <aside class="grid gap-5">
      <div class="card p-6" style="background:var(--blue-50);border-color:var(--blue-100)">
        <div class="flex items-center gap-3 mb-3">
          <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-none" style="background:#fff;color:var(--blue-600);box-shadow:var(--shadow-sm)">
            <svg style="width:19px;height:19px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.6-3.6A2 2 0 0019 5H5a2 2 0 00-2 2v8a2 2 0 002 2h11.7l2.7 2.7v-12z"/></svg>
          </span>
          <h3 class="text-[15px] font-bold" style="color:var(--navy-900)">Jaga Keamanan Akun Anda</h3>
        </div>
        <p class="text-[12.5px] leading-relaxed" style="color:var(--ink-600)">Password yang kuat akan melindungi data pribadi dan informasi penting di SIMPEG RSKK.</p>

        <div class="mt-5 grid gap-3.5">
          @foreach ([
            ['shield-check', 'var(--teal-600)', 'var(--teal-50)', 'Data Lebih Aman', 'Mencegah akses tidak sah ke akun Anda.'],
            ['lock', 'var(--blue-600)', 'var(--blue-50)', 'Privasi Terjaga', 'Melindungi informasi kepegawaian Anda.'],
            ['key', 'var(--amber-600)', 'var(--amber-50)', 'Akses Lebih Stabil', 'Mengurangi risiko penyalahgunaan akun.'],
          ] as [$ic, $fg, $bg, $tt, $dd])
            <div class="flex items-start gap-3">
              <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-none" style="background:{{ $bg }};color:{{ $fg }}">
                @if($ic === 'shield-check')
                  <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.6-3.6A2 2 0 0019 5H5a2 2 0 00-2 2v8a2 2 0 002 2h11.7l2.7 2.7v-12z"/></svg>
                @elseif($ic === 'lock')
                  <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                @else
                  <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a3 3 0 113-3m-6 13a3 3 0 113 3m-8-5a3 3 0 11-3-3"/></svg>
                @endif
              </span>
              <div class="min-w-0">
                <div class="text-[13px] font-semibold" style="color:var(--ink-900)">{{ $tt }}</div>
                <div class="text-[12px] leading-relaxed mt-0.5" style="color:var(--ink-500)">{{ $dd }}</div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center gap-2.5 mb-4">
          <svg style="width:16px;height:16px;color:var(--blue-600)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <h3 class="text-[14px] font-bold" style="color:var(--navy-900)">Tips Keamanan</h3>
        </div>
        <ul class="tips-list" style="list-style:none">
          <li>
            <svg style="width:13px;height:13px;flex:none;margin-top:2px;color:var(--green-600)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Gunakan password minimal 8 karakter yang mudah diingat namun sulit ditebak.
          </li>
          <li>
            <svg style="width:13px;height:13px;flex:none;margin-top:2px;color:var(--green-600)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Jangan gunakan password yang sama untuk akun lain atau data pribadi Anda.
          </li>
          <li>
            <svg style="width:13px;height:13px;flex:none;margin-top:2px;color:var(--green-600)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Jangan membagikan password kepada siapa pun, termasuk petugas IT.
          </li>
        </ul>
      </div>
    </aside>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  var form = document.getElementById('form-password');
  if(!form) return;

  form.querySelectorAll('[data-eye]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var input = btn.closest('.pw-input').querySelector('input');
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.querySelector('.eye-open').style.display = show ? 'none' : '';
      btn.querySelector('.eye-off').style.display = show ? '' : 'none';
      btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
      input.focus();
    });
  });

  var pw = form.querySelector('[data-strength]');
  var meter = pw ? pw.closest('div').parentElement.querySelector('.fs-meter') : null;
  var segs = meter ? meter.querySelectorAll('.strength-seg i') : [];
  var txt = document.getElementById('pw-strength-txt');
  if(pw && meter && segs.length && txt){
    var COLORS = {
      Lemah:  'var(--red-600)',
      Sedang: 'var(--amber-600)',
      Baik:   'var(--blue-600)',
      Kuat:   'var(--green-600)'
    };
    pw.addEventListener('input', function(){
      var v = pw.value;
      if(!v){ meter.style.display = 'none'; return; }
      meter.style.display = 'block';
      var s = 0;
      if(v.length >= 8) s++;
      if(v.length >= 12) s++;
      if(/[0-9]/.test(v)) s++;
      if(/[A-Za-z]/.test(v) && /[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
      if(/[^A-Za-z0-9]/.test(v)) s++;
      var label = s <= 1 ? 'Lemah' : s <= 3 ? 'Sedang' : s <= 4 ? 'Baik' : 'Kuat';
      var color = COLORS[label], lit = s <= 1 ? 1 : s <= 3 ? 2 : s <= 4 ? 3 : 4;
      txt.textContent = label;
      txt.style.color = color;
      segs.forEach(function(seg, i){
        seg.style.background = i < lit ? color : 'var(--line-soft)';
      });
    });
  }

  var cf = form.querySelector('[data-confirm]');
  var mw = document.getElementById('pw-match');
  var mwTxt = document.getElementById('pw-match-txt');
  if(cf && mw && mwTxt){
    function check(){
      var a = pw ? pw.value : '', b = cf.value;
      if(!b){ mw.style.display = 'none'; return; }
      mw.style.display = 'flex';
      if(a === b){ mw.className = 'pw-match ok'; mwTxt.textContent = 'Password cocok.'; }
      else { mw.className = 'pw-match bad'; mwTxt.textContent = 'Password tidak cocok.'; }
    }
    cf.addEventListener('input', check);
    if(pw) pw.addEventListener('input', check);
  }
})();
</script>
@endpush