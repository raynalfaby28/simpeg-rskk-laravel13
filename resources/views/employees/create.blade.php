@extends('layouts.app')
@section('title', 'Tambah Pegawai')
@section('nav-employees', 'active')
@section('crumb', 'Kepegawaian')

@section('content')
@php $employee = null; @endphp

<div class="page-head">
  <div>
    <h1 class="page-title">Tambah Pegawai</h1>
    <p class="page-desc">Cukup isi data awal. Data lengkap dapat dilengkapi pegawai melalui halaman profil.</p>
  </div>
  <a href="{{ route('employees.index') }}" class="btn-outline px-4 py-2 rounded-lg text-[12.5px] font-medium">&larr; Batal</a>
</div>

@if ($errors->any())
  <div class="mb-5 px-4 py-3.5 rounded-xl flex items-start gap-3 max-w-3xl" role="alert"
       style="background:#FFFBEB; border:1px solid #FDE68A; color:#92400E">
    <svg style="width:19px;height:19px;flex:none;margin-top:1px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
    </svg>
    <div class="text-[12.5px] leading-relaxed">
      <div class="font-semibold mb-0.5">Data belum dapat disimpan</div>
      <ul class="list-disc pl-4 space-y-0.5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  </div>
@endif

<form method="POST" action="{{ route('employees.store') }}">
  @csrf

  {{-- ===== Card 1: Data Identitas (wajib + opsional awal) ===== --}}
  <div class="card p-6 mb-5 max-w-3xl">
    <div class="mb-4">
      <h3 class="section-title mb-1">Data Identitas</h3>
      <p class="text-[12px]" style="color:var(--ink-500)">Hanya <b>NIP</b> dan <b>Nama Lengkap</b> yang wajib. Kolom lain boleh dikosongkan bila belum tersedia.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        @include('employees._field', ['name' => 'nip', 'label' => 'NIP', 'required' => true, 'help' => 'NIP dipakai sebagai username login dan tidak boleh sama.'])
      </div>
      <div>
        @include('employees._field', ['name' => 'nama_lengkap', 'label' => 'Nama Lengkap', 'required' => true])
      </div>
      <div>
        @include('employees._field', ['name' => 'nama_panggilan', 'label' => 'Nama Panggilan'])
      </div>
      <div>
        <label for="jenis_kelamin" class="flabel">Jenis Kelamin</label>
        <select id="jenis_kelamin" name="jenis_kelamin" class="input">
          <option value="">Pilih</option>
          <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
          <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
        </select>
      </div>
      <div class="md:col-span-2">
        @include('employees._field', ['name' => 'nik', 'label' => 'NIK / No. KTP', 'help' => 'Bila diisi, NIK tidak boleh sama dengan pegawai lain.'])
      </div>
    </div>

    <div class="mt-5 p-4 rounded-xl" style="background:var(--blue-50); border:1px solid var(--blue-100)">
      <div class="text-[11.5px] font-semibold mb-1.5" style="color:var(--blue-800)">Dapat dilengkapi nanti oleh pegawai</div>
      <div class="text-[11.5px] leading-relaxed" style="color:var(--ink-600)">
        Gelar · Tempat/Tanggal Lahir · Agama · Status Perkawinan · Golongan Darah · Alamat · Kontak · Pendidikan · Keluarga · Dokumen · Riwayat Kepegawaian
      </div>
    </div>
  </div>

  {{-- ===== Card 2: Role Akun (terpisah) ===== --}}
  <div class="card p-6 mb-5 max-w-3xl">
    <div class="mb-4">
      <h3 class="section-title mb-1">Role Akun</h3>
      <p class="text-[12px]" style="color:var(--ink-500)">Akun login dibuat otomatis saat pegawai disimpan — tidak perlu diisi manual.</p>
    </div>

    @if (auth()->user()->isSuperAdmin())
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">
        <label class="role-opt flex items-start gap-3 p-3.5 rounded-xl cursor-pointer" data-role="user">
          <input type="radio" name="role" value="user" class="mt-0.5" @checked(old('role', 'user') === 'user') style="accent-color:var(--blue-600)">
          <span>
            <span class="block text-[13px] font-semibold" style="color:var(--ink-800)">User (Pegawai)</span>
            <span class="block text-[11.5px]" style="color:var(--ink-500)">Hanya dapat melihat &amp; mengelola data dirinya sendiri.</span>
          </span>
        </label>
        <label class="role-opt flex items-start gap-3 p-3.5 rounded-xl cursor-pointer" data-role="admin">
          <input type="radio" name="role" value="admin" class="mt-0.5" @checked(old('role') === 'admin') style="accent-color:var(--blue-600)">
          <span>
            <span class="block text-[13px] font-semibold" style="color:var(--ink-800)">Admin</span>
            <span class="block text-[11.5px]" style="color:var(--ink-500)">Dapat mengelola data pegawai, dokumen, dan approval.</span>
          </span>
        </label>
      </div>
      <p class="text-[11.5px] mb-4" style="color:var(--ink-400)">Hanya Super Admin yang dapat menentukan role akun pegawai baru.</p>
    @else
      <div class="flex items-center gap-3 p-3.5 rounded-xl mb-4" style="border:1.5px solid var(--line)">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold" style="background:var(--blue-50); color:var(--blue-700); border:1px solid var(--blue-100)">User (Pegawai)</span>
        <span class="text-[11.5px]" style="color:var(--ink-500)">Role bawaan. Hanya Super Admin yang dapat mengubah role akun.</span>
      </div>
    @endif

    <div class="p-4 rounded-xl flex items-start gap-3" style="background:var(--blue-50); border:1px solid var(--blue-100)">
      <svg style="width:17px;height:17px;flex:none;margin-top:1px;color:var(--blue-600)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <div class="text-[12px] space-y-1" style="color:var(--ink-600)">
        <div>&bull; Username login = <b>NIP</b></div>
        <div>&bull; Password awal = <b>password123</b> <span style="color:var(--ink-400)">(Harap diubah pegawai setelah masuk)</span></div>
        <div>&bull; Role akun = <b id="role-label">{{ auth()->user()->isSuperAdmin() && old('role') === 'admin' ? 'Admin' : 'User (Pegawai)' }}</b>, status aktif</div>
        <div>&bull; Akun otomatis terdaftar di <b>Manajemen Akun</b></div>
      </div>
    </div>
  </div>

  <div class="flex items-center justify-between mb-8 max-w-3xl">
    <div class="flex items-center gap-3 text-[12px]" style="color:var(--ink-400)">
      <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      Data kepegawaian lain dapat dilengkapi pegawai via halaman Profil Saya.
    </div>
    <button type="submit" class="btn-primary px-7 py-2.5 rounded-lg text-sm font-medium">Simpan Pegawai</button>
  </div>
</form>

<style>
  .role-opt { border:1.5px solid var(--line); transition: border-color .15s, background .15s; }
  .role-opt:hover { border-color: var(--blue-300, #93C5FD); background: var(--blue-50); }
  .role-opt.selected { border-color: var(--blue-600); background: var(--blue-50); }
</style>
<script>
  (function(){
    var opts = document.querySelectorAll('.role-opt');
    if (!opts.length) return;
    var label = document.getElementById('role-label');
    var names = { user: 'User (Pegawai)', admin: 'Admin' };

    function sync(){
      opts.forEach(function(o){
        var r = o.querySelector('input[type=radio]');
        o.classList.toggle('selected', r.checked);
      });
      var checked = document.querySelector('.role-opt input:checked');
      if (label && checked) label.textContent = names[checked.value] || 'User (Pegawai)';
    }

    opts.forEach(function(o){ o.addEventListener('click', function(){ setTimeout(sync, 0); }); });
    sync();
  })();
</script>

@endsection
