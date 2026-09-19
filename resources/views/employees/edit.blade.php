@extends('layouts.app')
@section('title', 'Edit Profil Pegawai')
@section('nav-employees', 'active')
@section('crumb', 'Kepegawaian')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Edit Profil — {{ $employee->nama_lengkap }}</h1>
    <p class="page-desc">NIP {{ $employee->nip }} · Perubahan langsung memperbarui data resmi.</p>
  </div>
  <a href="{{ route('employees.show', $employee) }}" class="btn-outline px-4 py-2 rounded-lg text-[12.5px] font-medium">&larr; Batal</a>
</div>

@if ($errors->any())
  <div class="mb-5 text-xs px-4 py-3 rounded-lg" style="background:var(--red-50); color:var(--red-600); border:1px solid var(--red-100)">
    <ul class="list-disc pl-4">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('employees.update', $employee) }}">
  @csrf
  @method('PUT')

  {{-- ============ IDENTITAS PRIBADI ============ --}}
  <div class="card p-6 mb-5">
    <h3 class="section-title mb-3">IDENTITAS PRIBADI</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="md:col-span-2">
        @include('employees._field', ['name' => 'nama_lengkap', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true])
      </div>
      <div>
        @include('employees._field', ['name' => 'nama_panggilan', 'label' => 'Nama Panggilan'])
      </div>
      <div>
        @include('employees._field', ['name' => 'gelar_depan', 'label' => 'Gelar Depan'])
      </div>
      <div>
        @include('employees._field', ['name' => 'gelar_belakang', 'label' => 'Gelar Belakang'])
      </div>
      <div>
        @include('employees._field', ['name' => 'nik', 'label' => 'No. KTP / NIK'])
      </div>
      <div>
        @include('employees._field', ['name' => 'no_kk', 'label' => 'No. KK'])
      </div>
      <div>
        @include('employees._field', ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir'])
      </div>
      <div>
        @include('employees._field', ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date'])
      </div>
      <div>
        <label class="flabel">Jenis Kelamin</label>
        <select name="jenis_kelamin" class="input">
          <option value="">Pilih</option>
          <option value="L" @selected(old('jenis_kelamin', $employee->jenis_kelamin) === 'L')>Laki-laki</option>
          <option value="P" @selected(old('jenis_kelamin', $employee->jenis_kelamin) === 'P')>Perempuan</option>
        </select>
      </div>
      <div>
        <label class="flabel">Agama</label>
        <select name="agama" class="input">
          <option value="">Pilih</option>
          @foreach (['Islam','Kristen','Katolik','Hindu','Buddha','Khonghucu','Lainnya'] as $agama)
            <option value="{{ $agama }}" @selected(old('agama', $employee->agama) === $agama)>{{ $agama }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="flabel">Status Perkawinan</label>
        <select name="status_perkawinan" class="input">
          <option value="">Pilih</option>
          @foreach (['Belum Kawin','Kawin','Cerai Hidup','Cerai Mati'] as $s)
            <option value="{{ $s }}" @selected(old('status_perkawinan', $employee->status_perkawinan) === $s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>
      <div>
        @include('employees._field', ['name' => 'golongan_darah', 'label' => 'Golongan Darah'])
      </div>
      <div>
        <label class="flabel">Jenis ASN</label>
        <select name="jenis_asn" class="input">
          <option value="">Pilih</option>
          @foreach (['PNS','PPPK'] as $j)
            <option value="{{ $j }}" @selected(old('jenis_asn', $employee->jenis_asn) === $j)>{{ $j }}</option>
          @endforeach
        </select>
      </div>
      <div>
        @include('employees._field', ['name' => 'status_calon', 'label' => 'Status Calon (CPNS/Calon PPPK)'])
      </div>
      <div>
        @include('employees._field', ['name' => 'kedudukan_pegawai', 'label' => 'Kedudukan Pegawai'])
      </div>
      <div class="md:col-span-3 flex items-center gap-3 pt-1">
        <label class="flex items-center gap-2 text-[12.5px] font-medium" style="color:var(--ink-700)">
          <input type="checkbox" name="kepemilikan_kpe" value="1" @checked(old('kepemilikan_kpe', $employee->kepemilikan_kpe)) style="accent-color:var(--teal-700)">
          Kepemilikan KPE (Kartu PNS Elektronik)
        </label>
      </div>
    </div>
  </div>

  {{-- ============ IDENTITAS ADMINISTRATIF ============ --}}
  <div class="card p-6 mb-5">
    <h3 class="section-title mb-3">IDENTITAS ADMINISTRATIF</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>@include('employees._field', ['name' => 'no_npwp', 'label' => 'No. NPWP'])</div>
      <div>@include('employees._field', ['name' => 'no_bpjs', 'label' => 'No. BPJS'])</div>
      <div>@include('employees._field', ['name' => 'no_karpeg', 'label' => 'No. KARPEG'])</div>
      <div>@include('employees._field', ['name' => 'no_karis_karsu', 'label' => 'No. KARIS/KARSU'])</div>
      <div>@include('employees._field', ['name' => 'no_taspen', 'label' => 'No. TASPEN'])</div>
      <div>@include('employees._field', ['name' => 'no_rekening', 'label' => 'No. Rekening'])</div>
      <div>@include('employees._field', ['name' => 'bank', 'label' => 'Bank'])</div>
      <div>
        <label class="flabel">BAPERTARUM</label>
        <select name="bapertarum" class="input">
          <option value="">Pilih</option>
          @foreach (['Sudah Diambil','Belum Diambil','Tidak Ada'] as $b)
            <option value="{{ $b }}" @selected(old('bapertarum', $employee->bapertarum) === $b)>{{ $b }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  {{-- ============ STATUS KEPEGAWAIAN ============ --}}
  <div class="card p-6 mb-5">
    <h3 class="section-title mb-3">STATUS KEPEGAWAIAN</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="flabel">Status Pegawai</label>
        <select name="status_pegawai" class="input">
          <option value="">Pilih</option>
          @foreach(['PNS', 'PPPK', 'Honorer', 'Kontrak', 'Lainnya'] as $sp)
            <option value="{{ $sp }}" @selected($employee->status_pegawai === $sp)>{{ $sp }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="flabel">Kategori Pegawai</label>
        <select name="employee_category_id" class="input">
          <option value="">Pilih</option>
          @foreach ($employeeCategories as $cat)
            <option value="{{ $cat->id }}" @selected(old('employee_category_id', $employee->employee_category_id) == $cat->id)>{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="flabel">Status Kerja</label>
        <select name="employment_status_id" class="input">
          <option value="">Pilih</option>
          @foreach ($employmentStatuses as $es)
            <option value="{{ $es->id }}" @selected(old('employment_status_id', $employee->employment_status_id) == $es->id)>{{ $es->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  {{-- ============ PENDIDIKAN RINGKAS ============ --}}
  <div class="card p-6 mb-5">
    <h3 class="section-title mb-3">PENDIDIKAN (RINGKAS)</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="flabel">Pendidikan Awal</label>
        <select name="pendidikan_awal_id" class="input">
          <option value="">Pilih</option>
          @foreach ($educationLevels as $el)
            <option value="{{ $el->id }}" @selected(old('pendidikan_awal_id', $employee->pendidikan_awal_id) == $el->id)>{{ $el->name }}</option>
          @endforeach
        </select>
      </div>
      <div>@include('employees._field', ['name' => 'tahun_pendidikan_awal', 'label' => 'Tahun Pendidikan Awal'])</div>
      <div>
        <label class="flabel">Pendidikan Akhir</label>
        <select name="pendidikan_akhir_id" class="input">
          <option value="">Pilih</option>
          @foreach ($educationLevels as $el)
            <option value="{{ $el->id }}" @selected(old('pendidikan_akhir_id', $employee->pendidikan_akhir_id) == $el->id)>{{ $el->name }}</option>
          @endforeach
        </select>
      </div>
      <div>@include('employees._field', ['name' => 'tahun_pendidikan_akhir', 'label' => 'Tahun Pendidikan Akhir'])</div>
      <div class="flex items-center gap-2 pt-5">
        <label class="flex items-center gap-2 text-[12.5px] font-medium" style="color:var(--ink-700)">
          <input type="checkbox" name="izin_pemakaian_gelar" value="1" @checked(old('izin_pemakaian_gelar', $employee->izin_pemakaian_gelar)) style="accent-color:var(--teal-700)">
          Izin Pemakaian Gelar
        </label>
      </div>
    </div>
  </div>

  {{-- ============ JABATAN & ORGANISASI ============ --}}
  <div class="card p-6 mb-5">
    <h3 class="section-title mb-3">JABATAN & ORGANISASI</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="flabel">Jenis Jabatan</label>
        <select name="jenis_jabatan" class="input">
          <option value="">Pilih</option>
          @foreach (['struktural','fungsional','pelaksana'] as $jj)
            <option value="{{ $jj }}" @selected(old('jenis_jabatan', $employee->jenis_jabatan) === $jj)>{{ ucfirst($jj) }}</option>
          @endforeach
        </select>
      </div>
      <div>@include('employees._field', ['name' => 'eselon', 'label' => 'Eselon'])</div>
      <div>@include('employees._field', ['name' => 'tmt_eselon', 'label' => 'TMT Eselon', 'type' => 'date'])</div>
      <div>
        <label class="flabel">Jabatan Saat Ini</label>
        <select name="current_position_id" class="input">
          <option value="">Pilih</option>
          @foreach ($positions as $pos)
            <option value="{{ $pos->id }}" @selected(old('current_position_id', $employee->current_position_id) == $pos->id)>{{ $pos->name }}</option>
          @endforeach
        </select>
      </div>
      <div>@include('employees._field', ['name' => 'tmt_jabatan', 'label' => 'TMT Jabatan', 'type' => 'date'])</div>
      <div>@include('employees._field', ['name' => 'tugas_tambahan_1', 'label' => 'Tugas Tambahan 1'])</div>
      <div>@include('employees._field', ['name' => 'tmt_tugas_tambahan_1', 'label' => 'TMT Tugas Tambahan 1', 'type' => 'date'])</div>
      <div>@include('employees._field', ['name' => 'tugas_tambahan_2', 'label' => 'Tugas Tambahan 2'])</div>
      <div>@include('employees._field', ['name' => 'tmt_tugas_tambahan_2', 'label' => 'TMT Tugas Tambahan 2', 'type' => 'date'])</div>
      <div>
        <label class="flabel">Unit Kerja</label>
        <select name="work_unit_id" class="input">
          <option value="">Pilih</option>
          @foreach ($workUnits as $wu)
            <option value="{{ $wu->id }}" @selected(old('work_unit_id', $employee->work_unit_id) == $wu->id)>{{ $wu->name }}</option>
          @endforeach
        </select>
      </div>
      <div>@include('employees._field', ['name' => 'tmt_skpd', 'label' => 'TMT Unit Kerja', 'type' => 'date'])</div>
      <div>@include('employees._field', ['name' => 'instansi_dipekerjakan', 'label' => 'Instansi Tempat Diperkerjakan'])</div>
    </div>
  </div>

  {{-- ============ PANGKAT, GOLONGAN & GAJI ============ --}}
  <div class="card p-6 mb-5">
    <h3 class="section-title mb-3">PANGKAT, GOLONGAN & GAJI</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="flabel">Golongan Awal</label>
        <select name="golongan_awal_id" class="input">
          <option value="">Pilih</option>
          @foreach ($ranks as $rank)
            <option value="{{ $rank->id }}" @selected(old('golongan_awal_id', $employee->golongan_awal_id) == $rank->id)>{{ $rank->golongan }} {{ $rank->pangkat ? '— '.$rank->pangkat : '' }}</option>
          @endforeach
        </select>
      </div>
      <div>@include('employees._field', ['name' => 'tmt_golongan_awal', 'label' => 'TMT Golongan Awal', 'type' => 'date'])</div>
      <div>
        <label class="flabel">Golongan Akhir</label>
        <select name="golongan_akhir_id" class="input">
          <option value="">Pilih</option>
          @foreach ($ranks as $rank)
            <option value="{{ $rank->id }}" @selected(old('golongan_akhir_id', $employee->golongan_akhir_id) == $rank->id)>{{ $rank->golongan }} {{ $rank->pangkat ? '— '.$rank->pangkat : '' }}</option>
          @endforeach
        </select>
      </div>
      <div>@include('employees._field', ['name' => 'tmt_golongan_akhir', 'label' => 'TMT Golongan Akhir', 'type' => 'date'])</div>
      <div>@include('employees._field', ['name' => 'masa_kerja_tahun', 'label' => 'Masa Kerja (tahun)'])</div>
      <div>@include('employees._field', ['name' => 'masa_kerja_bulan', 'label' => 'Masa Kerja (bulan)'])</div>
      <div>@include('employees._field', ['name' => 'gaji_pokok', 'label' => 'Gaji Pokok'])</div>
      <div>@include('employees._field', ['name' => 'tmt_gaji_berkala_terbaru', 'label' => 'TMT Gaji Berkala Terbaru', 'type' => 'date'])</div>
    </div>
  </div>

  {{-- ============ ALAMAT RUMAH ============ --}}
  <div class="card p-6 mb-5">
    <h3 class="section-title mb-3">ALAMAT RUMAH</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="md:col-span-3">@include('employees._field', ['name' => 'alamat_rumah', 'label' => 'Alamat'])</div>
      <div>@include('employees._field', ['name' => 'rt_rumah', 'label' => 'RT'])</div>
      <div>@include('employees._field', ['name' => 'rw_rumah', 'label' => 'RW'])</div>
      <div>@include('employees._field', ['name' => 'kelurahan_rumah', 'label' => 'Kelurahan/Desa'])</div>
      <div>@include('employees._field', ['name' => 'kecamatan_rumah', 'label' => 'Kecamatan'])</div>
      <div>@include('employees._field', ['name' => 'kabkota_rumah', 'label' => 'Kab/Kota'])</div>
      <div>@include('employees._field', ['name' => 'provinsi_rumah', 'label' => 'Provinsi'])</div>
      <div>@include('employees._field', ['name' => 'kodepos_rumah', 'label' => 'Kode Pos'])</div>
    </div>
  </div>

  {{-- ============ ALAMAT DOMISILI (KTP) ============ --}}
  <div class="card p-6 mb-5">
    <h3 class="section-title mb-3">ALAMAT DOMISILI (KTP)</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="md:col-span-3">@include('employees._field', ['name' => 'alamat_domisili_ktp', 'label' => 'Alamat KTP'])</div>
      <div>@include('employees._field', ['name' => 'rt_domisili', 'label' => 'RT'])</div>
      <div>@include('employees._field', ['name' => 'rw_domisili', 'label' => 'RW'])</div>
      <div>@include('employees._field', ['name' => 'kelurahan_domisili', 'label' => 'Kelurahan/Desa'])</div>
      <div>@include('employees._field', ['name' => 'kecamatan_domisili', 'label' => 'Kecamatan'])</div>
      <div>@include('employees._field', ['name' => 'kabkota_domisili', 'label' => 'Kab/Kota'])</div>
      <div>@include('employees._field', ['name' => 'provinsi_domisili', 'label' => 'Provinsi'])</div>
      <div>@include('employees._field', ['name' => 'kodepos_domisili', 'label' => 'Kode Pos'])</div>
    </div>
  </div>

  {{-- ============ KONTAK ============ --}}
  <div class="card p-6 mb-5">
    <h3 class="section-title mb-3">KONTAK</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>@include('employees._field', ['name' => 'telp', 'label' => 'Telepon'])</div>
      <div>@include('employees._field', ['name' => 'hp', 'label' => 'No. HP'])</div>
      <div>@include('employees._field', ['name' => 'email_pribadi', 'label' => 'Email Pribadi', 'type' => 'email'])</div>
      <div>@include('employees._field', ['name' => 'email_resmi', 'label' => 'Email Resmi', 'type' => 'email'])</div>
    </div>
  </div>

  <div class="flex gap-2 mb-8">
    <button type="submit" class="btn-primary px-5 py-2.5 rounded-lg text-sm font-medium">Simpan Perubahan</button>
    <a href="{{ route('employees.show', $employee) }}" class="btn-outline px-5 py-2.5 rounded-lg text-sm font-medium">Batal</a>
  </div>
</form>
@endsection