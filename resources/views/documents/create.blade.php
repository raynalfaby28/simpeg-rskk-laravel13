@extends('layouts.app')
@section('title', 'Unggah Dokumen')
@section('nav-documents', 'active')
@section('crumb', 'Dokumen Digital')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Unggah Dokumen</h1>
    <p class="page-desc">Format PDF/JPG/PNG, maksimal 5 MB per dokumen.</p>
  </div>
  <a href="{{ route('documents.index') }}" class="btn btn-outline btn-sm">&larr; Kembali</a>
</div>

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

<form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="card p-6 max-w-lg">
  @csrf

  @if($admin)
    <div class="mb-4">
      <label class="flabel">Pegawai <span class="req">*</span></label>
      <select name="employee_id" required class="input">
        <option value="">Pilih Pegawai</option>
        @foreach ($employees as $emp)
          <option value="{{ $emp->id }}" @selected(old('employee_id', $employee?->id) == $emp->id)>{{ $emp->nama_lengkap }} · {{ $emp->nip }}</option>
        @endforeach
      </select>
    </div>
  @else
    <div class="mb-4 px-4 py-3 rounded-lg text-[12.5px]" style="background:var(--teal-50); color:var(--teal-700); border:1px solid var(--teal-100)">
      Dokumen akan diunggah atas nama {{ auth()->user()->name }} dan menunggu verifikasi admin.
    </div>
  @endif

  <div class="mb-4">
    <label class="flabel">Jenis Dokumen <span class="req">*</span></label>
    <select name="jenis_dokumen" required class="input">
      <option value="">Pilih Jenis</option>
      @foreach (\App\Http\Controllers\DocumentController::KATEGORI as $kategori)
        <option value="{{ $kategori }}" @selected(old('jenis_dokumen') === $kategori)>{{ $kategori }}</option>
      @endforeach
    </select>
  </div>

  <div class="mb-4">
    <label class="flabel">Kelompok Arsip</label>
    <select name="kategori" class="input">
      @foreach (\App\Http\Controllers\DocumentController::KATEGORI_GRUP as $gKey => $gLabel)
        <option value="{{ $gKey }}" @selected(old('kategori') === $gKey)>{{ $gLabel }}</option>
      @endforeach
    </select>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
      <label class="flabel">Nomor Dokumen</label>
      <input type="text" name="no_dokumen" value="{{ old('no_dokumen') }}" class="input">
    </div>
    <div>
      <label class="flabel">Tanggal Dokumen</label>
      <input type="date" name="tanggal" value="{{ old('tanggal') }}" class="input">
    </div>
  </div>

  <div class="mb-4">
    <label class="flabel">Keterangan</label>
    <textarea name="keterangan" rows="2" class="input">{{ old('keterangan') }}</textarea>
  </div>

  <div class="mb-5">
    <label class="flabel">File <span class="req">*</span></label>
    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" class="input">
  </div>

  <button type="submit" class="btn btn-primary">Unggah Dokumen</button>
</form>
@endsection