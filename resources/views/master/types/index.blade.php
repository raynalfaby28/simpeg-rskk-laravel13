@extends('layouts.app')
@section('title', 'Jenis Master Data')
@section('nav-master', 'active')
@section('crumb', 'Master Data')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Jenis Master Data</h1>
    <p class="page-desc">Kelola kelompok referensi yang tersedia — tambahkan jenis baru sesuai kebutuhan.</p>
  </div>
  <div class="flex items-center gap-2">
    <a href="{{ route('master.index', 'units') }}" class="btn btn-outline btn-sm">Data Master</a>
    <a href="{{ route('master.types.create') }}" class="btn btn-primary btn-sm">
      <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
      Tambah Jenis
    </a>
  </div>
</div>

@if(session('success'))
  <div class="alert mb-5" style="background:var(--green-50);border-color:var(--green-100);color:var(--green-700)">
    <svg style="width:16px;height:16px;flex:none;margin-top:1px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>{{ session('success') }}</span>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger mb-5">
    <svg style="width:16px;height:16px;flex:none;margin-top:1px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>{{ session('error') }}</span>
  </div>
@endif

<div class="card overflow-hidden">
  @if($types->isEmpty())
    <div class="p-10 text-center text-sm" style="color:var(--ink-500)">
      Belum ada jenis master data tambahan.
    </div>
  @else
    <div class="table-wrap">
      <table class="w-full text-sm">
        <thead>
          <tr class="table-th">
            <th class="px-4 py-3 text-left font-medium">#</th>
            <th class="px-4 py-3 text-left font-medium">Nama Jenis</th>
            <th class="px-4 py-3 text-left font-medium">Kode</th>
            <th class="px-4 py-3 text-left font-medium">Keterangan</th>
            <th class="px-4 py-3 text-center font-medium">Jumlah Data</th>
            <th class="px-4 py-3 text-left font-medium">Status</th>
            <th class="px-4 py-3 text-right font-medium">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($types as $i => $t)
            <tr class="row-line">
              <td class="table-td px-4 py-3" style="color:var(--ink-500)">{{ $i + 1 }}</td>
              <td class="table-td px-4 py-3 font-medium" style="color:var(--ink-800)">{{ $t->label }}</td>
              <td class="table-td px-4 py-3" style="color:var(--ink-500)">{{ $t->key }}</td>
              <td class="table-td px-4 py-3" style="color:var(--ink-600)">{{ $t->description ?: '—' }}</td>
              <td class="table-td px-4 py-3 text-center">
                <span class="font-semibold" style="color:var(--ink-700)">{{ $t->items_count }}</span>
              </td>
              <td class="table-td px-4 py-3">
                <span class="badge" style="background:var(--green-50);color:var(--green-600);border-color:var(--green-100)">
                  <span class="dot" style="background:var(--green-600)"></span>Aktif
                </span>
              </td>
              <td class="table-td px-4 py-3 text-right whitespace-nowrap">
                <a href="{{ route('master.index', $t->key) }}" class="btn btn-outline btn-sm mr-1">Kelola</a>
                <a href="{{ route('master.types.edit', $t) }}" class="btn btn-outline btn-sm mr-1">Edit</a>
                <form method="POST" action="{{ route('master.types.destroy', $t) }}" class="inline"
                  onsubmit="return confirm('Yakin hapus jenis master data «{{ $t->label }}» ini? Data di dalamnya akan ikut dihapus.')">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-sm">Hapus</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection