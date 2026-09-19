@extends('layouts.app')
@section('title', (isset($row) ? 'Edit ' : 'Tambah ') . $conf['label'])
@section('nav-employees', 'active')
@section('crumb', 'Profil Pegawai')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">{{ isset($row) ? 'Edit ' : 'Tambah ' }}{{ $conf['label'] }}</h1>
    <p class="page-desc">
      {{ $employee->nama_lengkap }} · NIP {{ $employee->nip }}
    </p>
  </div>
  <a href="{{ route('employees.show', $employee) }}" class="btn-outline px-4 py-2 rounded-lg text-[12.5px] font-medium">&larr; Kembali ke Profil</a>
</div>

<form method="POST" action="{{ isset($row) ? route('sub.update', [$employee, $type, $row->id]) : route('sub.store', [$employee, $type]) }}" enctype="multipart/form-data">
  @csrf
  @if(isset($row)) @method('PUT') @endif

  @if ($errors->any())
    <div class="alert alert-danger mb-4 text-xs">
      <ul class="list-disc pl-4">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card p-6 max-w-3xl">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4">
      @foreach ($conf['fields'] as $name => $field)
        <div class="{{ ($field['cols'] ?? 1) > 1 ? 'md:col-span-2' : '' }}">
          @php
            $value = old($name, $row?->{$name});
            if ($field['type'] === 'date' && $value && !is_string($value)) { $value = $value->format('Y-m-d'); }
          @endphp

          <label class="flabel">{{ $field['label'] }} @if(!empty($field['required']))<span class="req">*</span>@endif</label>

          @if($field['type'] === 'select')
            <select name="{{ $name }}" class="input" @if(!empty($field['required']))required @endif>
              <option value="">Pilih</option>
              @foreach (($options[$name] ?? $field['options'] ?? []) as $optVal => $optLabel)
                <option value="{{ $optVal }}" @selected((string) $value === (string) $optVal)>{{ $optLabel }}</option>
              @endforeach
            </select>

          @elseif($field['type'] === 'textarea')
            <textarea name="{{ $name }}" rows="3" class="input">{{ $value }}</textarea>

          @elseif($field['type'] === 'checkbox')
            <label class="flex items-center gap-2 text-[13px]" style="color:var(--ink-700)">
              <input type="checkbox" name="{{ $name }}" value="1" @checked((bool) $value)
                class="w-4 h-4 rounded" style="accent-color:var(--blue-600)">
              Ya, termasuk dalam tanggungan
            </label>

          @elseif($field['type'] === 'file')
            <input type="file" name="{{ $name }}" accept=".pdf,.jpg,.jpeg,.png" class="input file:mr-3 file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:rounded-lg file:font-semibold file:text-[12px] file:text-blue-600 file:cursor-pointer">
            @if($row && $row->{$name})
              <div class="text-[11.5px] mt-1.5">
                <span style="color:var(--ink-500)">Terkait:</span>
                <a href="{{ route('sub.download', [$type, $row->id]) }}" class="font-semibold" style="color:var(--blue-600)">Unduh Dokumen Terlampir</a>
              </div>
            @endif

          @else
            <input type="{{ $field['type'] === 'date' ? 'date' : ($field['type'] === 'number' ? 'number' : 'text') }}"
              name="{{ $name }}" value="{{ $value }}" @if(!empty($field['required']))required @endif
              @if($field['type'] === 'number' && $name === 'nilai') step="0.01" min="0" max="100" @endif
              class="input">
          @endif
        </div>
      @endforeach
    </div>

    <div class="flex items-center justify-end gap-2 mt-6 pt-5 border-t" style="border-color:var(--line-soft)">
      <a href="{{ route('employees.show', $employee) }}" class="btn-outline px-4 py-2 rounded-lg text-[12.5px] font-medium">Batal</a>
      <button class="btn-primary px-5 py-2 rounded-lg text-[12.5px] font-medium">{{ isset($row) ? 'Simpan Perubahan' : 'Simpan ' . $conf['label'] }}</button>
    </div>
  </div>
</form>
@endsection