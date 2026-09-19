<div class="tab-panel hidden" id="tab-keluarga">
  @include('employees._section', [
    'title' => 'Anggota Keluarga',
    'createUrl' => $admin ? route('sub.create', [$e, 'keluarga']) : null,
    'createLabel' => 'Tambah Anggota',
  ])
  @if($e->families->isEmpty())
    <div class="empty-state">
      <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
      <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Belum Ada Data Keluarga</p>
      <p class="text-[12.5px] mt-1">Pasangan, anak, orang tua, dan saudara dicatat di sini.</p>
    </div>
  @else
    @foreach (['pasangan' => 'Suami / Istri', 'anak' => 'Anak', 'orang_tua' => 'Orang Tua', 'saudara' => 'Saudara'] as $grp => $grpLabel)
      @php $members = $e->families->where('type', $grp); @endphp
      @if($members->isNotEmpty())
        <h3 class="section-title mb-3 mt-6">{{ $grpLabel }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
          @foreach ($members as $f)
            <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
              <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-2.5">
                  <span class="w-9 h-9 rounded-full flex items-center justify-center" style="background:var(--teal-50); color:var(--teal-600)">👤</span>
                  <div>
                    <div class="text-[13.5px] font-semibold">{{ $f->nama }}</div>
                    <div class="text-[12px]" style="color:var(--ink-500)">{{ $f->status ?: ucfirst($f->type) }}</div>
                  </div>
                </div>
                <div class="flex gap-2 text-[11.5px]">
                  @if($admin)
                    <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'keluarga', $f->id]) }}">Edit</a>
                    <form method="POST" action="{{ route('sub.destroy', [$e, 'keluarga', $f->id]) }}" onsubmit="return confirm('Hapus anggota keluarga ini?')">
                      @csrf @method('DELETE')
                      <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                    </form>
                  @endif
                </div>
              </div>
              <div class="grid grid-cols-2 gap-x-4 mt-3 pt-3 text-[11.5px]" style="border-top:1px solid var(--line-soft)">
                <div><span style="color:var(--ink-300)">NIK</span><br><span style="color:var(--ink-700)">{{ $f->nik ?? '' }}</span></div>
                <div><span style="color:var(--ink-300)">Lahir</span><br><span style="color:var(--ink-700)">{{ trim(($f->tempat_lahir ?? '') . ', ' . ($f->tanggal_lahir?->format('d M Y') ?? '')) ?: '' }}</span></div>
                <div><span style="color:var(--ink-300)">Pekerjaan</span><br><span style="color:var(--ink-700)">{{ $f->pekerjaan ?? '' }}</span></div>
                <div><span style="color:var(--ink-300)">Tanggungan</span><br>
                  <span class="badge" style="background:{{ $f->status_tanggungan ? 'var(--green-50)' : 'var(--line-soft)' }}; color:{{ $f->status_tanggungan ? 'var(--green-600)' : 'var(--ink-500)' }}; border-color:{{ $f->status_tanggungan ? 'var(--green-100)' : 'var(--line)' }}">
                    {{ $f->status_tanggungan ? 'Tertanggung' : 'Tidak' }}
                  </span>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    @endforeach
  @endif
</div>