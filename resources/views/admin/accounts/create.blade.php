@extends('layouts.app')
@section('title', 'Tambah Akun')
@section('nav-accounts', 'active')
@section('crumb', 'Manajemen Akun')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Tambah Akun Pegawai</h1>
    <p class="page-desc">Buat akun login baru untuk pegawai.</p>
  </div>
  <a href="{{ route('admin.accounts.index') }}" class="btn btn-outline btn-sm">&larr; Kembali</a>
</div>

<div class="card p-6 max-w-xl">
  <form method="POST" action="{{ route('admin.accounts.store') }}">
    @csrf

    @if ($errors->any())
      <div class="alert alert-danger mb-5">
        <svg style="width:16px;height:16px;flex:none;margin-top:1px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
          <div class="font-medium mb-0.5">Periksa kembali isian berikut</div>
          <ul class="list-disc pl-4">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif

    <div class="mb-4">
      <label class="flabel">NIP <span class="req">*</span></label>
      <input type="text" name="nip" value="{{ old('nip') }}" required class="input">
    </div>

    <div class="mb-4">
      <label class="flabel">Nama Lengkap <span class="req">*</span></label>
      <input type="text" name="name" value="{{ old('name') }}" required class="input">
    </div>

    <div class="mb-4">
      <label class="flabel">Email (opsional)</label>
      <input type="email" name="email" value="{{ old('email') }}" class="input">
    </div>

    <div class="mb-4">
      <label class="flabel">Role</label>
      <select name="role" class="input">
        @if(auth()->user()->role === 'super_admin')
          <option value="super_admin">Super Admin</option>
          <option value="admin">Admin</option>
          <option value="user" selected>User (Pegawai)</option>
        @else
          <option value="user" selected>User (Pegawai)</option>
        @endif
      </select>
    </div>

    <div class="mb-5">
      <label class="flabel">Password Awal <span class="req">*</span></label>
      <input type="password" name="password" required class="input">
    </div>

    <div class="flex gap-2">
      <button type="submit" class="btn btn-primary">Simpan &amp; Aktifkan Akun</button>
      <a href="{{ route('admin.accounts.index') }}" class="btn btn-outline">Batal</a>
    </div>
  </form>
</div>
@endsection