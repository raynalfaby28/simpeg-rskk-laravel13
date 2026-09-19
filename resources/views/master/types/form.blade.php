@extends('layouts.app')
@section('title', isset($masterType) ? 'Edit Jenis Master Data' : 'Tambah Jenis Master Data')
@section('nav-master', 'active')
@section('crumb', 'Master Data')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">{{ isset($masterType) ? 'Edit Jenis Master Data' : 'Tambah Jenis Master Data' }}</h1>
    <p class="page-desc">Jenis baru akan langsung tampil di sidebar &amp; daftar Master Data untuk diisi datanya.</p>
  </div>
  <a href="{{ route('master.types.index') }}" class="btn btn-outline btn-sm">&larr; Kembali</a>
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

<form method="POST" action="{{ isset($masterType) ? route('master.types.update', $masterType) : route('master.types.store') }}" class="card p-6 max-w-lg">
  @csrf
  @if(isset($masterType)) @method('PUT') @endif

  <div class="grid grid-cols-1 gap-4">
    <div>
      <label class="flabel">Nama Jenis <span class="req">*</span></label>
      <input type="text" name="label" value="{{ old('label', $masterType->label ?? '') }}" required placeholder="Contoh: Agama" class="input">
    </div>

    <div>
      <label class="flabel">Keterangan</label>
      <textarea name="description" rows="2" class="input" placeholder="Opsional — penjelasan singkat jenis ini">{{ old('description', $masterType->description ?? '') }}</textarea>
    </div>

    <div>
      <label class="flabel">Urutan Tampil</label>
      <input type="number" name="sort" min="0" max="999" value="{{ old('sort', $masterType->sort ?? 100) }}" class="input">
      <p class="text-[11.5px] mt-1" style="color:var(--ink-400)">Semakin kecil angkanya, semakin atas posisinya di daftar sidebar.</p>
    </div>
  </div>

  <div class="flex gap-2 mt-6">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('master.types.index') }}" class="btn btn-outline">Batal</a>
  </div>
</form>
@endsection