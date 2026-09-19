@extends('layouts.app')
@section('title', isset($row) ? 'Edit '.$conf['label'] : 'Tambah '.$conf['label'])
@section('nav-master', 'active')
@section('crumb', 'Master Data')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">{{ isset($row) ? 'Edit '.$conf['label'] : 'Tambah '.$conf['label'] }}</h1>
    <p class="page-desc">Referensi {{ $conf['label'] }} dipakai pada modul kepegawaian.</p>
  </div>
  <a href="{{ route('master.index', $type) }}" class="btn btn-outline btn-sm">&larr; Kembali</a>
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

<form method="POST" action="{{ isset($row) ? route('master.update', [$type, $row->id]) : route('master.store', $type) }}" class="card p-6 max-w-lg">
  @csrf
  @if(isset($row)) @method('PUT') @endif

  <div class="grid grid-cols-1 gap-4">
    @foreach ($conf['fields'] as $field)
      @php
        $value = old($field['name'], $row->{$field['name']} ?? null);
      @endphp
      <div>
        <label class="flabel">
          {{ $field['label'] }}
          @if(!empty($field['required'])) <span class="req">*</span> @endif
        </label>

        @if (($field['type'] ?? '') === 'boolean')
          <label class="flex items-center gap-2 text-[13px] font-medium" style="color:var(--ink-700)">
            <input type="checkbox" name="{{ $field['name'] }}" value="1" @checked($value) style="accent-color:var(--teal-700)">
            Aktif
          </label>
        @elseif (($field['type'] ?? '') === 'enum')
          <select name="{{ $field['name'] }}" class="input">
            @foreach ($field['options'] as $val => $label)
              <option value="{{ $val }}" @selected($value == $val)>{{ $label }}</option>
            @endforeach
          </select>
        @elseif (($field['type'] ?? '') === 'select')
          <select name="{{ $field['name'] }}" class="input">
            <option value="">{{ $field['empty'] ?? 'Pilih' }}</option>
            @foreach ($options[$field['name']] ?? [] as $optId => $optName)
              <option value="{{ $optId }}" @selected($value == $optId)>{{ $optName }}</option>
            @endforeach
          </select>
        @elseif (($field['type'] ?? '') === 'textarea')
          <textarea name="{{ $field['name'] }}" rows="2" class="input"
            @if(!empty($field['required'])) required @endif>{{ $value }}</textarea>
        @else
          <input type="{{ $field['type'] ?? 'text' }}" name="{{ $field['name'] }}" value="{{ $value }}"
            @if(!empty($field['required'])) required @endif
            class="input">
        @endif
      </div>
    @endforeach
  </div>

  <div class="flex gap-2 mt-6">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('master.index', $type) }}" class="btn btn-outline">Batal</a>
  </div>
</form>
@endsection