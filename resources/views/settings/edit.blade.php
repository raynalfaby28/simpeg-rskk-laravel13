@extends('layouts.app')
@section('title', 'Pengaturan Sistem')
@section('crumb', 'Pengaturan')
@section('nav-settings', 'active')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Pengaturan Sistem</h1>
    <p class="page-desc">Nama aplikasi, identitas rumah sakit, dan informasi kontak.</p>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('settings.update') }}" class="card p-6 max-w-2xl" enctype="multipart/form-data">
  @csrf
  @method('PUT')

  <h3 class="section-title mb-4">Foto Halaman Login</h3>
  <p class="text-[12.5px] mb-4" style="color:var(--text-muted)">Foto/ilustrasi yang tampil di kiri halaman login. Gunakan PNG dengan latar transparan agar menyatu dengan panel.</p>

  <div class="flex items-center gap-5 mb-6 flex-wrap">
    <div>
      <img id="login-photo-preview" src="{{ $loginPhotoUrl ?? asset('assets/images/login-maskot.png') }}"
           alt="Foto halaman login" style="width:150px;height:150px;object-fit:contain;border-radius:14px;background:var(--line-soft)">
    </div>
    <div class="flex-1 min-w-[220px]">
      <label class="flabel mb-2">Foto Halaman Login</label>
      <input type="file" name="login_photo" id="login-photo-input" accept="image/png,image/jpeg,image/webp" class="input">
      <div class="flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary btn-sm">Upload Foto</button>
        @if ($loginPhotoUrl)
          <button type="button" class="btn btn-outline btn-sm" style="color:#B91C1C" onclick="document.getElementById('reset-photo-form').submit()">Kembalikan ke Bawaan</button>
        @endif
      </div>
      <p class="text-[11.5px] mt-2" style="color:var(--text-muted)">Format: PNG / JPG / WebP, maksimal 8 MB.</p>
    </div>
  </div>

  <h3 class="section-title mb-4">Aplikasi</h3>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4 mb-6">
    <label class="flabel">Nama Aplikasi</label>
    <input type="text" name="app_name" value="{{ $settings['app_name'] ?? '' }}" class="input">
    <label class="flabel">Tagline Aplikasi</label>
    <input type="text" name="app_tagline" value="{{ $settings['app_tagline'] ?? '' }}" class="input">
  </div>

  <h3 class="section-title mb-4">Identitas Rumah Sakit</h3>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4 mb-6">
    <label class="flabel">Nama Rumah Sakit</label>
    <input type="text" name="rs_name" value="{{ $settings['rs_name'] ?? '' }}" class="input">
    <label class="flabel">Telepon</label>
    <input type="text" name="rs_phone" value="{{ $settings['rs_phone'] ?? '' }}" class="input">
    <label class="flabel">Email</label>
    <input type="email" name="rs_email" value="{{ $settings['rs_email'] ?? '' }}" class="input">
    <div class="md:col-span-2">
      <label class="flabel">Alamat</label>
      <textarea name="rs_address" rows="3" class="input">{{ $settings['rs_address'] ?? '' }}</textarea>
    </div>
  </div>

  <div class="flex items-center justify-end gap-2 pt-5 border-t" style="border-color:var(--line-soft)">
    <button class="btn btn-primary">Simpan Pengaturan</button>
  </div>
</form>

<form id="reset-photo-form" method="POST" action="{{ route('settings.photo-delete') }}" style="display:none">
  @csrf
</form>

@push('scripts')
<script>
  var input = document.getElementById('login-photo-input');
  input.addEventListener('change', function () {
    var file = input.files[0];
    if (!file) return;
    if (!['image/png', 'image/jpeg', 'image/webp'].includes(file.type)) {
      alert('Format foto harus PNG, JPG, atau WebP.');
      input.value = '';
      return;
    }
    if (file.size > 8 * 1024 * 1024) {
      alert('Ukuran foto maksimal 8 MB.');
      input.value = '';
      return;
    }
    var reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById('login-photo-preview').src = e.target.result;
    };
    reader.readAsDataURL(file);
  });
</script>
@endpush
@endsection