<div class="tab-panel hidden" id="tab-kinerja-penghargaan">
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
      <div class="flex items-center justify-between mb-4">
        <h3 class="section-title mb-0">Penilaian Kinerja</h3>
        @if($admin)
          <a href="{{ route('sub.create', [$e, 'kinerja']) }}" class="btn-outline px-3 py-1.5 rounded-lg text-[12px] font-medium flex-none">+ Tambah</a>
        @endif
      </div>
      @if($e->performances->isEmpty())
        <div class="empty-state"><p class="text-[13px]">Belum ada penilaian kinerja.</p></div>
      @else
        <div class="grid grid-cols-1 gap-3">
          @foreach ($e->performances->sortByDesc('tahun') as $p)
            <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
              <div class="flex items-center justify-between mb-2">
                <div class="text-[13.5px] font-semibold">Kinerja {{ $p->tahun ?? '' }}</div>
                <span class="badge" style="background:{{ ($p->nilai ?? 0) >= 75 ? 'var(--green-50)' : 'var(--amber-50)' }}; color:{{ ($p->nilai ?? 0) >= 75 ? 'var(--green-600)' : 'var(--amber-600)' }}; border-color:{{ ($p->nilai ?? 0) >= 75 ? 'var(--green-100)' : 'var(--amber-100)' }}">{{ $p->predikat ?? '' }}</span>
              </div>
              @if($p->nilai !== null)
                <div class="flex items-center gap-2 mb-1">
                  <div class="progress flex-1"><div style="width:{{ min($p->nilai, 100) }}%; background:linear-gradient(90deg, var(--teal-500), var(--blue-600))"></div></div>
                  <span class="text-[13px] font-bold" style="color:var(--blue-600)">{{ number_format($p->nilai, 0) }}%</span>
                </div>
              @endif
              @if($p->periode)<div class="text-[11.5px]" style="color:var(--ink-500)">Periode {{ $p->periode }}</div>@endif
              @if($p->pejabat_penilai)<div class="text-[11.5px]" style="color:var(--ink-500)">Pejabat Penilai: {{ $p->pejabat_penilai }}</div>@endif
              <div class="flex gap-2 mt-2 pt-2 text-[11.5px]" style="border-top:1px solid var(--line-soft)">
                @if($p->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['kinerja', $p->id]) }}">Unduh</a>@endif
                @if($admin)
                  <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'kinerja', $p->id]) }}">Edit</a>
                  <form method="POST" action="{{ route('sub.destroy', [$e, 'kinerja', $p->id]) }}" onsubmit="return confirm('Hapus penilaian ini?')">
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

    <div>
      <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft); min-height:100%">
        <div class="flex items-center justify-between mb-4">
          <h3 class="section-title mb-0">SKP</h3>
          @if($admin)
            <a href="{{ route('sub.create', [$e, 'skp']) }}" class="btn-outline px-3 py-1.5 rounded-lg text-[12px] font-medium flex-none">+ Tambah</a>
          @endif
        </div>
        @if($e->skps->isEmpty())
          <div class="empty-state"><p class="text-[13px]">Belum ada SKP.</p></div>
        @else
          <div class="grid grid-cols-1 gap-3">
            @foreach ($e->skps->sortByDesc('tahun') as $sk)
              <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
                <div class="flex items-center justify-between gap-2">
                  <div class="text-[13.5px] font-semibold">SKP {{ $sk->tahun ?? '' }}{{ $sk->periode ? ' · ' . $sk->periode : '' }}</div>
                  <span class="badge" style="background:var(--blue-50); color:var(--blue-600); border-color:var(--blue-100)">{{ $sk->predikat ?? '' }}</span>
                </div>
                @if($sk->uraian_kegiatan)<div class="text-[12px] mt-1.5" style="color:var(--ink-700)">{{ $sk->uraian_kegiatan }}</div>@endif
                @if($sk->nilai !== null)<div class="text-[12px] mt-1" style="color:var(--ink-500)">Nilai: <strong style="color:var(--blue-600)">{{ number_format($sk->nilai, 2, ',', '.') }}</strong></div>@endif
                <div class="flex gap-2 mt-2 pt-2 text-[11.5px]" style="border-top:1px solid var(--line-soft)">
                  @if($sk->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['skp', $sk->id]) }}">Unduh</a>@endif
                  @if($admin)
                    <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'skp', $sk->id]) }}">Edit</a>
                    <form method="POST" action="{{ route('sub.destroy', [$e, 'skp', $sk->id]) }}" onsubmit="return confirm('Hapus SKP ini?')">
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
  </div>

  {{-- Angka Kredit --}}
  <div class="mt-8">
    @include('employees._section', [
      'title' => 'Angka Kredit',
      'createUrl' => $admin ? route('sub.create', [$e, 'angka_kredit']) : null,
      'createLabel' => 'Tambah',
    ])
    @if($e->creditScores->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada pencatatan angka kredit.</p></div>
    @else
      <div class="table-wrap">
        <table class="w-full min-w-[680px]">
          <thead><tr>
            <th class="table-th">Tahun</th><th class="table-th">Unsur</th><th class="table-th">Butir Kegiatan</th>
            <th class="table-th">Nilai AK</th><th class="table-th">Keterangan</th><th class="table-th text-right">Aksi</th>
          </tr></thead>
          <tbody>
            @foreach ($e->creditScores->sortByDesc('tahun') as $cs)
              <tr class="row-line">
                <td class="table-td font-medium">{{ $cs->tahun ?? '' }}</td>
                <td class="table-td">{{ $cs->unsur ?? '' }}</td>
                <td class="table-td">{{ $cs->butir_kegiatan ?? '' }}</td>
                <td class="table-td">{{ $cs->nilai_angka_kredit !== null ? number_format($cs->nilai_angka_kredit, 2, ',', '.') : '' }}</td>
                <td class="table-td">{{ $cs->keterangan ?? '' }}</td>
                <td class="table-td text-right">
                  <div class="flex justify-end gap-2 text-[11.5px]">
                    @if($cs->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['angka_kredit', $cs->id]) }}">Unduh</a>@endif
                    @if($admin)
                      <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'angka_kredit', $cs->id]) }}">Edit</a>
                      <form method="POST" action="{{ route('sub.destroy', [$e, 'angka_kredit', $cs->id]) }}" onsubmit="return confirm('Hapus angka kredit ini?')">
                        @csrf @method('DELETE')
                        <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  {{-- IPASN --}}
  <div class="mt-10">
    @include('employees._section', [
      'title' => 'Indeks Profesionalitas ASN (IPASN)',
      'createUrl' => $admin ? route('sub.create', [$e, 'ipasn']) : null,
      'createLabel' => 'Tambah',
    ])
    @if($e->ipasns->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada catatan IPASN.</p></div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach ($e->ipasns->sortByDesc('tahun') as $ip)
          <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
            <div class="flex items-center justify-between gap-2">
              <div class="text-[13.5px] font-semibold">IPASN {{ $ip->tahun ?? '' }}</div>
              <span class="badge" style="background:var(--teal-50); color:var(--teal-600); border-color:var(--teal-100)">{{ $ip->predikat ?? '' }}</span>
            </div>
            <div class="text-[12px] mt-1.5" style="color:var(--ink-500)">{{ $ip->komponen ?? 'Total Indeks' }}</div>
            @if($ip->nilai !== null)<div class="text-[18px] font-bold mt-1" style="color:var(--blue-600)">{{ number_format($ip->nilai, 2, ',', '.') }}</div>@endif
            <div class="flex gap-2 mt-2 pt-2 text-[11.5px]" style="border-top:1px solid var(--line-soft)">
              @if($ip->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['ipasn', $ip->id]) }}">Unduh</a>@endif
              @if($admin)
                <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'ipasn', $ip->id]) }}">Edit</a>
                <form method="POST" action="{{ route('sub.destroy', [$e, 'ipasn', $ip->id]) }}" onsubmit="return confirm('Hapus catatan IPASN ini?')">
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

  {{-- Penghargaan --}}
  <div class="mt-10">
    @include('employees._section', [
      'title' => 'Penghargaan',
      'createUrl' => $admin ? route('sub.create', [$e, 'penghargaan']) : null,
      'createLabel' => 'Tambah',
    ])
    @if($e->awards->isEmpty())
      <div class="empty-state">
        <div class="es-ic"><svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 3a4 4 0 00-7 2.6L12 18l3-2.4L18 5.6A4 4 0 0016 3zM12 15l-3.5 6 2-4h3l2 4-3.5-6z"/></svg></div>
        <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">Belum Ada Penghargaan</p>
        <p class="text-[12.5px] mt-1">Penghargaan & piagam yang diterima pegawai dicatat di sini.</p>
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($e->awards as $award)
          <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
            <div class="flex items-center justify-between mb-2">
              <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background:var(--amber-50); color:var(--amber-600)">🏅</div>
              <div class="flex gap-2 text-[11.5px]">
                @if($award->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['penghargaan', $award->id]) }}">Unduh</a>@endif
                @if($admin)
                  <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'penghargaan', $award->id]) }}">Edit</a>
                  <form method="POST" action="{{ route('sub.destroy', [$e, 'penghargaan', $award->id]) }}" onsubmit="return confirm('Hapus penghargaan ini?')">
                    @csrf @method('DELETE')
                    <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                  </form>
                @endif
              </div>
            </div>
            <div class="text-[13.5px] font-semibold">{{ $award->nama_penghargaan }}</div>
            <div class="text-[12px]" style="color:var(--ink-500)">
              Tahun {{ $award->tahun ?? '' }}{{ $award->pemberi_penghargaan ? ' · ' . $award->pemberi_penghargaan : '' }}
            </div>
            @if($award->no_penghargaan)<div class="text-[11.5px] mt-1" style="color:var(--ink-500)">Piagam: {{ $award->no_penghargaan }}</div>@endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
</div>