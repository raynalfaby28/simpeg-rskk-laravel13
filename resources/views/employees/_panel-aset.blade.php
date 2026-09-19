<div class="tab-panel hidden" id="tab-aset">
  @include('employees._section', [
    'title' => 'Aset Pegawai',
    'sub' => 'Aset milik Rumah Sakit yang diserahkan / dibawa pulang oleh pegawai (kendaraan, elektronik, perabot, dan lainnya).',
    'createUrl' => $admin ? route('sub.create', [$e, 'aset']) : null,
    'createLabel' => 'Tambah Aset',
  ])
  @php
    $kondisiPill = fn ($k) => match ($k) {
      'Baik' => ['var(--green-50)', 'var(--green-600)', 'var(--green-100)'],
      'Cukup Baik' => ['var(--blue-50)', 'var(--blue-600)', 'var(--blue-100)'],
      'Rusak Ringan' => ['var(--amber-50)', 'var(--amber-600)', 'var(--amber-100)'],
      'Rusak Berat' => ['var(--red-50)', 'var(--red-600)', 'var(--red-100)'],
      default => ['var(--navy-50, #EEF1F6)', 'var(--ink-500)', 'var(--line)'],
    };
    $statusPill = fn ($s) => match ($s) {
      'Masih Dipakai' => ['var(--green-50)', 'var(--green-600)', 'var(--green-100)'],
      'Mutasi Aset' => ['var(--amber-50)', 'var(--amber-600)', 'var(--amber-100)'],
      'Dikembalikan' => ['var(--ink-50)', 'var(--ink-500)', 'var(--line)'],
      default => ['var(--blue-50)', 'var(--blue-600)', 'var(--blue-100)'],
    };
  @endphp
  @if($e->assets->isEmpty())
    <div class="empty-state">
      <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7M4 7l3-3m-3 3h16M17 4l3 3M8 21h8"/></svg></div>
      <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Belum Ada Aset</p>
      <p class="text-[12.5px] mt-1">Aset RS yang dipegang pegawai belum tercatat.</p>
    </div>
  @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
      @foreach ($e->assets->sortByDesc('tanggal_mulai') as $a)
        <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="text-[13.5px] font-semibold truncate">{{ $a->nama_aset }}</div>
              <div class="text-[12px] mt-0.5" style="color:var(--ink-500)">{{ $a->assetType?->name ?? 'Aset' }}</div>
            </div>
            <div class="flex gap-2 text-[11.5px] flex-none">
              @if($a->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['aset', $a->id]) }}">BAST</a>@endif
              @if($admin)
                <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'aset', $a->id]) }}">Edit</a>
                <form method="POST" action="{{ route('sub.destroy', [$e, 'aset', $a->id]) }}" onsubmit="return confirm('Hapus aset ini?')">
                  @csrf @method('DELETE')
                  <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                </form>
              @endif
            </div>
          </div>
          @php [$kBg, $kFg, $kBd] = $kondisiPill($a->kondisi); @endphp
          @php [$sBg, $sFg, $sBd] = $statusPill($a->status); @endphp
          <div class="flex flex-wrap items-center gap-1.5 mt-3">
            @if($a->kondisi)<span class="badge" style="background:{{ $kBg }}; color:{{ $kFg }}; border-color:{{ $kBd }}">Kondisi: {{ $a->kondisi }}</span>@endif
            <span class="badge" style="background:{{ $sBg }}; color:{{ $sFg }}; border-color:{{ $sBd }}">{{ $a->status ?? 'Masih Dipakai' }}</span>
          </div>
          <div class="grid grid-cols-2 gap-x-4 gap-y-1 mt-3 pt-3 text-[11.5px]" style="border-top:1px solid var(--line-soft)">
            @if($a->merk)<div><span style="color:var(--ink-300)">Merk</span><br><span style="color:var(--ink-700)">{{ $a->merk }}</span></div>@endif
            @if($a->no_seri)<div><span style="color:var(--ink-300)">No. Seri</span><br><span style="color:var(--ink-700)">{{ $a->no_seri }}</span></div>@endif
            @if($a->no_inventaris)<div><span style="color:var(--ink-300)">No. Inventaris</span><br><span style="color:var(--ink-700)">{{ $a->no_inventaris }}</span></div>@endif
            <div><span style="color:var(--ink-300)">Dipakai</span><br><span style="color:var(--ink-700)">{{ optional($a->tanggal_mulai)->format('d M Y') ?? '—' }}</span></div>
            <div><span style="color:var(--ink-300)">Selesai</span><br><span style="color:var(--ink-700)">{{ optional($a->tanggal_selesai)->format('d M Y') ?? ($a->status === 'Masih Dipakai' ? 'Masih dipakai' : '—') }}</span></div>
          </div>
          @if($a->surat_mutasi_path || $a->surat_pengembalian_path)
            <div class="flex flex-wrap gap-2 mt-2 pt-2 text-[11px]" style="border-top:1px solid var(--line-soft)">
              @if($a->surat_mutasi_path)<a class="font-semibold" style="color:var(--amber-600)" href="{{ route('sub.download', ['aset', $a->id, 'field' => 'surat_mutasi_path']) }}">Surat Mutasi</a>@endif
              @if($a->surat_pengembalian_path)<a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.download', ['aset', $a->id, 'field' => 'surat_pengembalian_path']) }}">Surat Pengembalian</a>@endif
            </div>
          @endif
          @if($a->keterangan)<div class="text-[11.5px] mt-2 pt-2" style="border-top:1px solid var(--line-soft); color:var(--ink-500)">{{ $a->keterangan }}</div>@endif
        </div>
      @endforeach
    </div>
  @endif
</div>