@php
  $eduFormal = $e->educations->where('kategori', '!=', 'non_formal');
  $eduNonFormal = $e->educations->where('kategori', 'non_formal');
  $diklatCat = [
    'struktural' => 'Diklat Struktural',
    'fungsional' => 'Diklat Fungsional',
    'teknis' => 'Diklat Teknis',
    'keahlian_profesi' => 'Sertifikat Keahlian / Profesi',
    'bintek_seminar' => 'Bimbingan Teknis / Seminar',
    'lainnya' => 'Diklat Lainnya',
  ];
  $diklatGroups = [];
  foreach ($diklatCat as $k => $label) {
    $items = $k === 'lainnya'
      ? $e->trainings->filter(fn ($t) => !in_array($t->kategori, array_keys($diklatCat), true) || $t->kategori === 'lainnya')
      : $e->trainings->where('kategori', $k);
    if ($items->isNotEmpty()) { $diklatGroups[$k] = ['label' => $label, 'items' => $items]; }
  }
@endphp
<div class="tab-panel hidden" id="tab-pendidikan-diklat">

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
      <div class="flex items-center justify-between mb-4">
        <h3 class="section-title mb-0">Pendidikan Formal</h3>
        @if($admin)
          <a href="{{ route('sub.create', [$e, 'pendidikan']) }}" class="btn-outline px-3 py-1.5 rounded-lg text-[12px] font-medium flex-none">+ Tambah</a>
        @endif
      </div>
      @if($eduFormal->isEmpty())
        <div class="empty-state"><p class="text-[13px]">Belum ada riwayat pendidikan formal.</p></div>
      @else
        <div class="timeline">
          @foreach ($eduFormal->sortByDesc('tahun_masuk') as $edu)
            <div class="tl-item">
              <span class="tl-dot"></span>
              <div class="tl-year">{{ $edu->tahun_masuk }} – {{ $edu->tahun_lulus ?? 'sekarang' }}</div>
              <div class="text-[14px] font-semibold mt-0.5">{{ $edu->educationLevel?->name ?? '' }}</div>
              <div class="text-[12.5px]" style="color:var(--ink-500)">
                {{ $edu->institution ?? '' }}{{ $edu->major ? ' · ' . $edu->major : '' }}
                @if($edu->no_ijazah)<div class="mt-0.5 text-[11.5px]">No. Ijazah: {{ $edu->no_ijazah }}</div>@endif
              </div>
              <div class="flex gap-1.5 mt-2 text-[11.5px]">
                @if($edu->file_ijazah_path)
                  <a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['pendidikan', $edu->id]) }}">Unduh Ijazah</a>
                @endif
                @if($admin)
                  <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'pendidikan', $edu->id]) }}">Edit</a>
                  <form method="POST" action="{{ route('sub.destroy', [$e, 'pendidikan', $edu->id]) }}" onsubmit="return confirm('Hapus riwayat pendidikan ini?')">
                    @csrf @method('DELETE')
                    <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                  </form>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
      <h3 class="section-title mb-4">Pendidikan Non Formal</h3>
      @if($eduNonFormal->isEmpty())
        <div class="empty-state"><p class="text-[13px]">Belum ada pendidikan non formal.</p></div>
      @else
        <div class="timeline">
          @foreach ($eduNonFormal->sortByDesc('tahun_masuk') as $edu)
            <div class="tl-item">
              <span class="tl-dot"></span>
              <div class="tl-year">{{ $edu->tahun_masuk }} – {{ $edu->tahun_lulus ?? 'sekarang' }}</div>
              <div class="text-[14px] font-semibold mt-0.5">{{ $edu->educationLevel?->name ?? '' }}</div>
              <div class="text-[12.5px]" style="color:var(--ink-500)">
                {{ $edu->institution ?? '' }}{{ $edu->major ? ' · ' . $edu->major : '' }}
                @if($edu->no_ijazah)<div class="mt-0.5 text-[11.5px]">No. Ijazah: {{ $edu->no_ijazah }}</div>@endif
              </div>
              <div class="flex gap-1.5 mt-2 text-[11.5px]">
                @if($edu->file_ijazah_path)
                  <a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['pendidikan', $edu->id]) }}">Unduh</a>
                @endif
                @if($admin)
                  <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'pendidikan', $edu->id]) }}">Edit</a>
                  <form method="POST" action="{{ route('sub.destroy', [$e, 'pendidikan', $edu->id]) }}" onsubmit="return confirm('Hapus riwayat pendidikan ini?')">
                    @csrf @method('DELETE')
                    <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                  </form>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  <div class="mt-10">
    @include('employees._section', [
      'title' => 'Diklat & Pelatihan',
      'sub' => 'Diklat struktural, fungsional, teknis, sertifikat keahlian/profesi, serta bimbingan teknis & seminar.',
      'createUrl' => $admin ? route('sub.create', [$e, 'diklat']) : null,
      'createLabel' => 'Tambah Diklat',
    ])
    @if($e->trainings->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada diklat tercatat.</p></div>
    @else
      @foreach($diklatGroups as $gIdx => $grp)
        <h3 class="section-title mb-3 mt-7 first:mt-0">{{ $grp['label'] }}</h3>
        <div class="space-y-3 mb-5">
          @foreach ($grp['items']->sortByDesc('tanggal_selesai') as $t)
            <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
              <div class="flex items-start justify-between gap-2">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-none" style="background:var(--teal-50); color:var(--teal-600)">🎓</div>
                <div class="min-w-0 flex-1">
                  <div class="text-[13.5px] font-semibold">{{ $t->nama_pelatihan }}</div>
                  <div class="text-[12px]" style="color:var(--ink-500)">{{ $t->penyelenggara ?? '' }} · {{ optional($t->tanggal_mulai)->format('M Y') ?? '' }}{{ $t->tanggal_selesai ? ' – ' . optional($t->tanggal_selesai)->format('M Y') : '' }}</div>
                  @if($t->no_sertifikat)<div class="text-[11.5px] mt-1" style="color:var(--ink-500)">Sertifikat: {{ $t->no_sertifikat }}</div>@endif
                  <div class="flex gap-2 mt-2 text-[11.5px]">
                    @if($t->file_sertifikat_path)
                      <a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['diklat', $t->id]) }}">Unduh Sertifikat</a>
                    @endif
                    @if($admin)
                      <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'diklat', $t->id]) }}">Edit</a>
                      <form method="POST" action="{{ route('sub.destroy', [$e, 'diklat', $t->id]) }}" onsubmit="return confirm('Hapus diklat ini?')">
                        @csrf @method('DELETE')
                        <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                      </form>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endforeach
    @endif
  </div>

  <div class="mt-2">
    @include('employees._section', [
      'title' => 'Riwayat Bahasa',
      'sub' => 'Bahasa yang dikuasai pegawai beserta tingkat & kemampuan.',
      'createUrl' => $admin ? route('sub.create', [$e, 'bahasa']) : null,
      'createLabel' => 'Tambah Bahasa',
    ])
    @if($e->languages->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada riwayat bahasa.</p></div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach ($e->languages as $lang)
          <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
            <div class="flex items-start justify-between gap-2">
              <div class="text-[13.5px] font-semibold">{{ $lang->nama_bahasa }}</div>
              <div class="flex gap-2 text-[11.5px]">
                @if($admin)
                  <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'bahasa', $lang->id]) }}">Edit</a>
                  <form method="POST" action="{{ route('sub.destroy', [$e, 'bahasa', $lang->id]) }}" onsubmit="return confirm('Hapus riwayat bahasa ini?')">
                    @csrf @method('DELETE')
                    <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                  </form>
                @endif
              </div>
            </div>
            @if($lang->tingkat) <div class="mt-1"><span class="badge" style="background:var(--blue-50); color:var(--blue-600); border-color:var(--blue-100)">{{ $lang->tingkat }}</span></div> @endif
            <div class="text-[11.5px] mt-1.5" style="color:var(--ink-500)">Kemampuan: {{ $lang->kemampuan ?? '-' }}</div>
            @if($lang->keterangan)<div class="text-[11.5px] mt-1" style="color:var(--ink-500)">{{ $lang->keterangan }}</div>@endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
</div>