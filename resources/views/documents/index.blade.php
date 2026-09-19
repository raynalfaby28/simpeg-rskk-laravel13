@extends('layouts.app')
@section('title', 'Dokumen Digital')
@section('nav-documents', 'active')
@section('crumb', $admin ? 'Kepegawaian' : 'Saya')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Dokumen Digital</h1>
    <p class="page-desc">
      {{ $admin ? 'Arsip dokumen seluruh pegawai dan status verifikasinya.' : 'Unggah dan pantau status verifikasi dokumen kamu.' }}
    </p>
  </div>
  <a href="{{ route('documents.create') }}" class="btn-primary px-4 py-2 rounded-lg text-[12.5px] font-medium">+ Unggah Dokumen</a>
</div>

@if(session('error'))
  <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

@if($admin)
  {{-- Filter status + search untuk admin --}}
  <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
    <div class="flex gap-1.5 flex-wrap">
      @foreach (['semua' => 'Semua', 'belum_diverifikasi' => 'Perlu Verifikasi', 'terverifikasi' => 'Terverifikasi', 'ditolak' => 'Ditolak'] as $key => $label)
        @php $isActive = request('status') === $key || ($key === 'semua' && !request('status')); @endphp
        <a href="{{ route('documents.index', ['status' => $key === 'semua' ? null : $key] + (request('kategori') ? ['kategori' => request('kategori')] : [])) }}"
           class="chip {{ $isActive ? 'active' : '' }}"
           style="{{ $isActive ? 'border-color:var(--blue-600)' : '' }}">
          {{ $label }} <span class="opacity-70">({{ $totals[$key] }})</span>
        </a>
      @endforeach
    </div>
    <form method="GET" class="flex gap-2 flex-wrap">
      @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
      @if(request('kategori'))<input type="hidden" name="kategori" value="{{ request('kategori') }}">@endif
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari pegawai / NIP / jenis dokumen..." class="input" style="width:18rem">
      <button class="btn btn-outline">Cari</button>
      @if(request('q'))<a href="{{ route('documents.index') }}" class="btn btn-outline">Reset</a>@endif
    </form>
  </div>

  {{-- Chips kategori (hanya yang punya dokumen) --}}
  @if($kategoriList->isNotEmpty())
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 mb-4">
      <span class="text-[11px] font-semibold uppercase tracking-wide flex-none" style="color:var(--ink-300)">Kategori:</span>
      <a href="{{ route('documents.index', (request('status') ? ['status' => request('status')] : []) + (request('q') ? ['q' => request('q')] : [])) }}"
         class="chip rounded-full flex-none {{ !request('kategori') ? 'active' : '' }}"
         style="{{ !request('kategori') ? 'border-color:var(--blue-600)' : '' }}">
        Semua <span class="opacity-70">({{ $totals['semua'] }})</span>
      </a>
      @foreach ($kategoriList as $k)
        <a href="{{ route('documents.index', ['kategori' => $k['name']] + (request('status') ? ['status' => request('status')] : []) + (request('q') ? ['q' => request('q')] : [])) }}"
           class="chip rounded-full flex-none {{ request('kategori') === $k['name'] ? 'active' : '' }}"
           style="{{ request('kategori') === $k['name'] ? 'border-color:var(--blue-600)' : '' }}">
          {{ $k['name'] }} <span class="opacity-70">({{ $k['total'] }})</span>
        </a>
      @endforeach
    </div>
  @endif
@endif

@if($documents->isEmpty())
  <div class="card p-10 text-center text-sm" style="color:var(--ink-500)">
    @if($admin) Belum ada dokumen yang sesuai filter. @else Belum ada dokumen diunggah. Gunakan tombol "Unggah Dokumen". @endif
  </div>
@else
  @if($admin)
    <div class="table-wrap">
      <table class="tbl min-w-[920px]">
        <thead>
          <tr>
            <th class="table-th">Pegawai</th>
            <th class="table-th">Dokumen</th>
            <th class="table-th">Diunggah</th>
            <th class="table-th">Status</th>
            <th class="table-th text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($documents as $doc)
            <tr class="row-line">
              <td class="table-td">
                <div class="font-medium" style="color:var(--ink-900)">{{ $doc->employee->nama_lengkap }}</div>
                <div class="trow-sub">NIP {{ $doc->employee->nip }}</div>
              </td>
              <td class="table-td">
                <div class="font-medium">{{ $doc->jenis_dokumen }}</div>
                @if($doc->no_dokumen)<div class="trow-sub">{{ $doc->no_dokumen }}</div>@endif
                @if($doc->status_verifikasi === 'ditolak' && $doc->keterangan)
                  <div class="trow-sub" style="color:var(--red-600)">Alasan: {{ $doc->keterangan }}</div>
                @endif
              </td>
              <td class="table-td" style="color:var(--ink-500)">
                <div>Diunggah {{ $doc->created_at->diffForHumans() }}</div>
                @if($doc->tanggal)<div class="trow-sub">Tertanggal {{ $doc->tanggal->format('d M Y') }}</div>@endif
              </td>
              <td class="table-td">
                @php
                  $pill = match ($doc->status_verifikasi) {
                    'terverifikasi' => ['bg' => 'var(--green-50)', 'fg' => 'var(--green-600)', 'bd' => 'var(--green-100)', 'txt' => 'Terverifikasi'],
                    'ditolak' => ['bg' => 'var(--red-50)', 'fg' => 'var(--red-600)', 'bd' => 'var(--red-100)', 'txt' => 'Ditolak'],
                    default => ['bg' => 'var(--amber-50)', 'fg' => 'var(--amber-600)', 'bd' => 'var(--amber-100)', 'txt' => 'Perlu Verifikasi'],
                  };
                @endphp
                <span class="badge" style="background:{{ $pill['bg'] }}; color:{{ $pill['fg'] }}; border-color:{{ $pill['bd'] }}">{{ $pill['txt'] }}</span>
              </td>
              <td class="table-td text-right whitespace-nowrap">
                <a href="{{ route('documents.download', $doc) }}" class="btn btn-outline btn-sm">Unduh</a>
                @if($doc->status_verifikasi !== 'terverifikasi')
                  <form method="POST" action="{{ route('documents.verify', $doc) }}" class="inline">
                    @csrf
                    <button class="btn btn-sm" style="background:var(--green-50); color:var(--green-600)">Verifikasi</button>
                  </form>
                @endif
                @if($doc->status_verifikasi !== 'ditolak')
                  <button data-reject="{{ $doc->id }}" data-name="{{ $doc->jenis_dokumen }}" class="btn btn-sm" style="background:var(--red-50); color:var(--red-600)">Tolak</button>
                @endif
                <form method="POST" action="{{ route('documents.destroy', $doc) }}" class="inline" onsubmit="return confirm('Hapus dokumen ini?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-outline btn-sm" style="color:var(--ink-500)">Hapus</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    {{-- Tampilan kartu untuk user --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach ($documents as $doc)
        <div class="card p-5 flex flex-col">
          <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:var(--teal-50); color:var(--teal-700)">
              <svg style="width:17px;height:17px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 3v6h6"/></svg>
            </div>
            @php
              $pill = match ($doc->status_verifikasi) {
                'terverifikasi' => ['bg' => 'var(--green-50)', 'fg' => 'var(--green-600)', 'bd' => 'var(--green-100)', 'txt' => 'Terverifikasi'],
                'ditolak' => ['bg' => 'var(--red-50)', 'fg' => 'var(--red-600)', 'bd' => 'var(--red-100)', 'txt' => 'Ditolak'],
                default => ['bg' => 'var(--amber-50)', 'fg' => 'var(--amber-600)', 'bd' => 'var(--amber-100)', 'txt' => 'Menunggu Verifikasi'],
              };
            @endphp
            <span class="badge" style="background:{{ $pill['bg'] }}; color:{{ $pill['fg'] }}; border-color:{{ $pill['bd'] }}">{{ $pill['txt'] }}</span>
          </div>
          <div class="font-semibold text-[14px]" style="color:var(--ink-900)">{{ $doc->jenis_dokumen }}</div>
          @if($doc->no_dokumen)<div class="text-[12px] mt-0.5" style="color:var(--ink-500)">{{ $doc->no_dokumen }}</div>@endif
          <div class="text-[11.5px] mt-0.5" style="color:var(--ink-300)">Diunggah {{ $doc->created_at->diffForHumans() }}</div>
          @if($doc->status_verifikasi === 'ditolak' && $doc->keterangan)
            <div class="text-[12px] mt-2 px-3 py-2 rounded-lg" style="background:var(--red-50); color:var(--red-600); border:1px solid #F4C0D1">{{ $doc->keterangan }}</div>
          @endif
          <div class="flex gap-2 mt-4 pt-3" style="border-top:1px solid var(--line)">
            <a href="{{ route('documents.download', $doc) }}" class="btn-outline flex-1 text-center px-3 py-2 rounded-lg text-[12px] font-medium">Download</a>
            <form method="POST" action="{{ route('documents.destroy', $doc) }}" onsubmit="return confirm('Hapus dokumen ini?')">
              @csrf @method('DELETE')
              <button class="px-3 py-2 rounded-lg text-[12px] font-medium" style="border:1px solid var(--line); color:var(--red-600)">Hapus</button>
            </form>
          </div>
        </div>
      @endforeach
    </div>
  @endif
  <div class="mt-5">{{ $documents->links() }}</div>
@endif

{{-- Modal tolak dokumen (admin) --}}
@if($admin)
<div id="modal-reject" class="modal-backdrop">
  <div class="modal" style="max-width:28rem">
    <h3 class="modal-title">Tolak Dokumen</h3>
    <p class="text-[12.5px] mb-3" style="color:var(--ink-500)">Alasan wajib diisi — akan dikirim ke pegawai.</p>
    <form method="POST" id="form-reject" action="">
      @csrf
      <textarea name="keterangan" rows="3" required class="input mb-3" style="resize:vertical; min-height:72px" placeholder="Contoh: Dokumen SK belum sesuai..."></textarea>
      <div class="flex gap-2 justify-end">
        <button type="button" id="batal-reject" class="btn btn-outline" onclick="closeModal('modal-reject')">Batal</button>
        <button type="submit" class="btn btn-danger">Tolak Dokumen</button>
      </div>
    </form>
  </div>
</div>
<script>
  const rejectForm = document.getElementById('form-reject');
  document.querySelectorAll('[data-reject]').forEach(btn => {
    btn.addEventListener('click', () => {
      rejectForm.action = '{{ url('dokumen') }}' + '/' + btn.dataset.reject + '/reject';
      openModal('modal-reject');
    });
  });
</script>
@endif
@endsection