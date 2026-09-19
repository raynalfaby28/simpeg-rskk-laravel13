@php
  $docGrups = \App\Http\Controllers\DocumentController::KATEGORI_GRUP;
  $docsByCat = $e->documents->groupBy(fn ($d) => $d->kategori ?: 'lainnya');
  $docsEmpty = $e->documents->isEmpty();
@endphp
<div class="tab-panel hidden" id="tab-dokumen">
  @include('employees._section', [
    'title' => 'Arsip Dokumen Digital',
    'sub' => 'Dokumen dikelompokkan per kategori arsip.',
    'createUrl' => $admin ? route('documents.create-admin', $e) : route('documents.create'),
    'createLabel' => 'Unggah Dokumen',
  ])

  @if($docsEmpty)
    <div class="empty-state">
      <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2zM15 3v6h6"/></svg></div>
      <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Belum Ada Dokumen</p>
      <p class="text-[12.5px] mt-1">Belum ada dokumen yang ditambahkan untuk pegawai ini.</p>
    </div>
  @else
    @foreach($docGrups as $gKey => $gLabel)
      @php $docs = $docsByCat->get($gKey, collect()); @endphp
      @if($docs->isNotEmpty())
        <h3 class="section-title mb-3 mt-8 first:mt-0">{{ $gLabel }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
          @foreach($docs as $doc)
            @php
              $pill = match ($doc->status_verifikasi) {
                'terverifikasi' => ['bg' => 'var(--green-50)', 'fg' => 'var(--green-600)', 'bd' => 'var(--green-100)', 'txt' => 'Terverifikasi'],
                'ditolak' => ['bg' => 'var(--red-50)', 'fg' => 'var(--red-600)', 'bd' => 'var(--red-100)', 'txt' => 'Ditolak'],
                default => ['bg' => 'var(--amber-50)', 'fg' => 'var(--amber-600)', 'bd' => 'var(--amber-100)', 'txt' => 'Perlu Verifikasi'],
              };
            @endphp
            <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
              <div class="flex items-start justify-between mb-2">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:var(--blue-50); color:var(--blue-600)">
                  <svg style="width:17px;height:17px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-6-6H7a2 2 0 00-2 2v14a2 2 0 002 2zM15 3v6h6"/></svg>
                </div>
                <span class="badge" style="background:{{ $pill['bg'] }}; color:{{ $pill['fg'] }}; border-color:{{ $pill['bd'] }}">{{ $pill['txt'] }}</span>
              </div>
              <div class="text-[13px] font-semibold">{{ $doc->jenis_dokumen }}</div>
              <div class="text-[11px]" style="color:var(--ink-500)">@if($doc->no_dokumen){{ $doc->no_dokumen }} @endif{{ optional($doc->tanggal)->format('d M Y') ?? '' }}</div>
              @if($doc->status_verifikasi === 'ditolak' && $doc->keterangan)
                <div class="text-[11.5px] mt-2 px-3 py-2 rounded-lg" style="background:var(--red-50); color:var(--red-600)">{{ $doc->keterangan }}</div>
              @endif
              <div class="flex gap-1.5 mt-3 pt-3" style="border-top:1px solid var(--line-soft)">
                <a href="{{ route('documents.download', $doc) }}" class="btn-outline flex-1 text-center px-3 py-1.5 rounded-lg text-[11.5px] font-medium">Unduh</a>
                @if($admin)
                  <a href="{{ route('documents.edit', $doc) }}" class="btn-outline text-center px-3 py-1.5 rounded-lg text-[11.5px] font-medium">Edit</a>
                  <form method="POST" action="{{ route('documents.destroy', $doc) }}" onsubmit="return confirm('Hapus dokumen ini?')">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 rounded-lg text-[11.5px] font-semibold" style="color:var(--red-600)">Hapus</button>
                  </form>
                @endif
                @if($doc->status_verifikasi !== 'terverifikasi' && $admin)
                  <form method="POST" action="{{ route('documents.verify', $doc) }}">
                    @csrf
                    <button class="px-3 py-1.5 rounded-lg text-[11.5px] font-medium" style="background:var(--green-50); color:var(--green-600)">Verifikasi</button>
                  </form>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @endif
    @endforeach
  @endif
</div>