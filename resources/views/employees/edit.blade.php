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
          @foreach(['PNS', 'PPPK', 'Honorer', 'Kontrak', 'Outsourcing'] as $sp)
            <option value="{{ $sp }}" @selected($employee->status_pegawai === $sp)>{{ $sp }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="flabel">Jenis Pekerjaan (Outsourcing)</label>
        <select name="outsourcing_job_id" class="input">
          <option value="">Pilih</option>
          @foreach ($outsourcingJobs as $oj)
            <option value="{{ $oj->id }}" @selected(old('outsourcing_job_id', $employee->outsourcing_job_id) == $oj->id)>{{ $oj->name }}</option>
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
    @php $lockedJabatan = filled($employee->current_position_id); $lockedUnit = filled($employee->work_unit_id); @endphp
    @if($lockedJabatan || $lockedUnit)
    <div class="alert alert-info mb-4" style="background:var(--blue-50);border-color:var(--blue-100);color:var(--blue-800)">
      <svg style="width:16px;height:16px;flex:none;margin-top:1px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
      <span>Jabatan dan Unit Kerja yang sudah terisi <strong>dikunci</strong> di halaman ini. Untuk mengubahnya, gunakan menu <strong>Riwayat Mutasi</strong> pada profil pegawai agar perubahan tetap tercatat.</span>
    </div>
    @endif
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="flabel">Jenis Jabatan</label>
        <select name="jenis_jabatan" class="input">
          <option value="">Pilih</option>
          @foreach ($positionTypes as $jj)
            <option value="{{ $jj->code }}" @selected(old('jenis_jabatan', $employee->jenis_jabatan) === $jj->code)>{{ $jj->name }}</option>
          @endforeach
        </select>
      </div>
      <div>@include('employees._field', ['name' => 'eselon', 'label' => 'Eselon'])</div>
      <div>@include('employees._field', ['name' => 'tmt_eselon', 'label' => 'TMT Eselon', 'type' => 'date'])</div>
      <div>
        <label class="flabel">Jabatan Saat Ini @if($lockedJabatan)<span class="badge" style="background:var(--amber-50);color:var(--amber-600);border-color:var(--amber-100)">Dikunci</span>@endif</label>
        <select name="current_position_id" class="input" @disabled($lockedJabatan) @if($lockedJabatan) style="background:#F8FAFC;color:var(--ink-700)" @endif>
          <option value="">Pilih</option>
          @foreach (['struktural', 'fungsional', 'pelaksana', 'outsourcing'] as $jj)
            <optgroup label="{{ ucfirst($jj) }}">
              @foreach ($positions->where('type', $jj) as $pos)
                <option value="{{ $pos->id }}" @selected(old('current_position_id', $employee->current_position_id) == $pos->id)>{{ $pos->name }}</option>
              @endforeach
            </optgroup>
          @endforeach
        </select>
        @if($lockedJabatan)
        <p class="text-[11px] mt-1.5 flex items-center gap-1.5" style="color:var(--amber-600)">
          <svg style="width:12px;height:12px;flex:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          Diubah melalui Riwayat Mutasi.
        </p>
        @endif
      </div>
      <div>@include('employees._field', ['name' => 'tmt_jabatan', 'label' => 'TMT Jabatan', 'type' => 'date'])</div>
      <div>@include('employees._field', ['name' => 'tugas_tambahan_1', 'label' => 'Tugas Tambahan 1'])</div>
      <div>@include('employees._field', ['name' => 'tmt_tugas_tambahan_1', 'label' => 'TMT Tugas Tambahan 1', 'type' => 'date'])</div>
      <div>@include('employees._field', ['name' => 'tugas_tambahan_2', 'label' => 'Tugas Tambahan 2'])</div>
      <div>@include('employees._field', ['name' => 'tmt_tugas_tambahan_2', 'label' => 'TMT Tugas Tambahan 2', 'type' => 'date'])</div>
      <div>
        <label class="flabel">Unit Kerja @if($lockedUnit)<span class="badge" style="background:var(--amber-50);color:var(--amber-600);border-color:var(--amber-100)">Dikunci</span>@endif</label>
        <select name="work_unit_id" class="input" @disabled($lockedUnit) @if($lockedUnit) style="background:#F8FAFC;color:var(--ink-700)" @endif>
          <option value="">Pilih</option>
          @foreach ($workUnits as $wu)
            <option value="{{ $wu->id }}" @selected(old('work_unit_id', $employee->work_unit_id) == $wu->id)>{{ $wu->name }}</option>
          @endforeach
        </select>
        @if($lockedUnit)
        <p class="text-[11px] mt-1.5 flex items-center gap-1.5" style="color:var(--amber-600)">
          <svg style="width:12px;height:12px;flex:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          Diubah melalui Riwayat Mutasi.
        </p>
        @endif
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

  {{-- ============ ALAMAT KTP ============ --}}
  <div class="card p-6 mb-5">
    <h3 class="section-title mb-3">ALAMAT KTP</h3>
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

  {{-- ============ ALAMAT DOMISILI ============ --}}
  <div class="card p-6 mb-5">
    <div class="flex items-center justify-between flex-wrap gap-2 mb-1">
      <h3 class="section-title">ALAMAT DOMISILI</h3>
      <label class="flex items-center gap-2 cursor-pointer select-none py-1 px-3 rounded-xl" style="border:1px solid var(--line); background:var(--surface)">
        <input type="checkbox" id="sama-alamat-ktp" class="sama-ktp">
        <span class="text-[13px] font-semibold" style="color:var(--ink-700)">Sama dengan Alamat KTP</span>
      </label>
    </div>
    <p class="text-[12px] mb-3" style="color:var(--ink-300)">Alamat domisili adalah tempat tinggal saat ini. Jika sama dengan alamat di KTP, centang <strong>Sama dengan Alamat KTP</strong> agar terisi otomatis. Jika berbeda, isi sendiri secara manual.</p>
    <div id="domisili-sama-hint" class="alert alert-info mb-4" style="display:none;background:var(--blue-50);border-color:var(--blue-100);color:var(--blue-800)">
      <svg style="width:15px;height:15px;flex:none;margin-top:1px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <span>Alamat domisili mengikuti Alamat KTP. Kosongkan centang jika ingin mengisi alamat domisili yang berbeda.</span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="md:col-span-3">@include('employees._field', ['name' => 'alamat_domisili_ktp', 'label' => 'Alamat Domisili'])</div>
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

<script>
(function () {
  var sp = document.querySelector('[name="status_pegawai"]');
  var osSelect = document.querySelector('[name="outsourcing_job_id"]');
  if (sp && osSelect) {
    function syncStatus() {
      var isOs = sp.value === 'Outsourcing';
      osSelect.disabled = false;
      osSelect.style.opacity = isOs ? '1' : '0.4';
      if (!isOs) { osSelect.value = ''; }
    }
    sp.addEventListener('change', syncStatus);
    syncStatus();
  }

  var pairs = [
    ['alamat_rumah', 'alamat_domisili_ktp'],
    ['rt_rumah', 'rt_domisili'],
    ['rw_rumah', 'rw_domisili'],
    ['kelurahan_rumah', 'kelurahan_domisili'],
    ['kecamatan_rumah', 'kecamatan_domisili'],
    ['kabkota_rumah', 'kabkota_domisili'],
    ['provinsi_rumah', 'provinsi_domisili'],
    ['kodepos_rumah', 'kodepos_domisili']
  ];
  var chk = document.getElementById('sama-alamat-ktp');
  if (!chk) return;
  var hint = document.getElementById('domisili-sama-hint');
  var domisiliInputs = pairs.map(function (p) { return document.querySelector('[name="' + p[1] + '"]'); });

  function copyToDomisili() {
    pairs.forEach(function (p) {
      var src = document.querySelector('[name="' + p[0] + '"]');
      var dst = document.querySelector('[name="' + p[1] + '"]');
      if (src && dst) dst.value = src.value;
    });
  }

  function applyToggle() {
    var same = chk.checked;
    hint.style.display = same ? 'flex' : 'none';
    domisiliInputs.forEach(function (el) {
      if (!el) return;
      el.disabled = same;
      el.style.background = same ? '#F8FAFC' : 'transparent';
      el.style.color = same ? 'var(--ink-400)' : 'inherit';
    });
    if (same) copyToDomisili();
  }

  chk.addEventListener('change', applyToggle);
  pairs.forEach(function (p) {
    var src = document.querySelector('[name="' + p[0] + '"]');
    if (src) src.addEventListener('input', function () { if (chk.checked) copyToDomisili(); });
  });

  var ktpFilled = (document.querySelector('[name="alamat_rumah"]') && document.querySelector('[name="alamat_rumah"]').value) ? true : false;
  var domisiliFilled = domisiliInputs.some(function (el) { return el && el.value; });
  if (!ktpFilled && !domisiliFilled) {
    chk.checked = true;
    applyToggle();
  }
})();
</script>
@endsection