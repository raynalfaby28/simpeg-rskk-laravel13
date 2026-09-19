<div class="tab-panel hidden" id="tab-kepegawaian">
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
    <div class="p-5 rounded-xl" style="border:1px solid var(--line)">
      <h3 class="section-title mb-4">Kondisi Saat Ini</h3>
      @include('employees._kv', ['items' => [
        'Jabatan' => $e->currentPosition?->name, 'Jenis Jabatan' => ucfirst($e->jenis_jabatan ?? ''),
        'Eselon' => $e->eselon ? $e->eselon . (optional($e->tmt_eselon)->format('d M Y') ? ' · TMT ' . $e->tmt_eselon->format('d M Y') : '') : null,
        'TMT Jabatan' => optional($e->tmt_jabatan)->format('d M Y'),
        'Unit Kerja' => $e->workUnit?->name, 'TMT SKPD' => optional($e->tmt_skpd)->format('d M Y'),
        'Golongan Awal' => $e->golonganAwal?->golongan . ($e->tmt_golongan_awal ? ' · TMT ' . $e->tmt_golongan_awal->format('d M Y') : ''),
        'Golongan Akhir' => $e->golonganAkhir?->golongan . ($e->tmt_golongan_akhir ? ' · TMT ' . $e->tmt_golongan_akhir->format('d M Y') : ''),
        'Masa Kerja' => $e->masa_kerja_tahun !== null ? $e->masa_kerja_tahun . ' th ' . ($e->masa_kerja_bulan ?? 0) . ' bln' : null,
        'TMT Gaji Berkala' => optional($e->tmt_gaji_berkala_terbaru)->format('d M Y'),
      ]])
    </div>
    <div class="p-5 rounded-xl" style="border:1px solid var(--line-soft)">
      <h3 class="section-title mb-4">Ringkasan Status</h3>
      @include('employees._kv', ['items' => [
        'Status Pegawai' => $e->status_pegawai, 'Jenis ASN' => $e->jenis_asn,
        'Kategori' => $e->employeeCategory?->name, 'Status Kerja' => $e->employmentStatus?->name,
        'Tugas Tambahan' => $e->tugas_tambahan_1 ? trim($e->tugas_tambahan_1 . (optional($e->tmt_tugas_tambahan_1)->format('d M Y') ? ' · TMT ' . $e->tmt_tugas_tambahan_1->format('d M Y') : '')) : null,
        'Gaji Pokok' => $e->gaji_pokok ? ($admin ? 'Rp ' . number_format($e->gaji_pokok, 0, ',', '.') : 'Rp ••••') : null,
      ]])
    </div>
  </div>

  {{-- Riwayat Jabatan --}}
  @include('employees._section', [
    'title' => 'Riwayat Jabatan',
    'sub' => null,
    'createUrl' => $admin ? route('sub.create', [$e, 'jabatan']) : null,
    'createLabel' => 'Tambah',
  ])
  @if($e->positionHistories->isEmpty())
    <div class="empty-state"><p class="text-[13px]">Belum ada riwayat jabatan.</p></div>
  @else
    <div class="timeline">
      @foreach ($e->positionHistories->sortByDesc('tmt') as $ph)
        <div class="tl-item">
          <span class="tl-dot"></span>
          <div class="tl-year">{{ optional($ph->tmt)->format('Y') }}</div>
          <div class="text-[14px] font-semibold mt-0.5">{{ $ph->position?->name ?? '' }}</div>
          <div class="text-[12px]" style="color:var(--ink-500)">
            TMT {{ optional($ph->tmt)->format('d M Y') ?? '' }}{{ $ph->workUnit?->name ? ' · ' . $ph->workUnit?->name : '' }}
            @if($ph->no_sk)<div class="mt-0.5">SK: {{ $ph->no_sk }}</div>@endif
          </div>
          <div class="flex gap-1.5 mt-2 text-[11.5px]">
            @if($ph->file_sk_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['jabatan', $ph->id]) }}">Unduh SK</a>@endif
            @if($admin)
              <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'jabatan', $ph->id]) }}">Edit</a>
              <form method="POST" action="{{ route('sub.destroy', [$e, 'jabatan', $ph->id]) }}" onsubmit="return confirm('Hapus riwayat jabatan ini?')">
                @csrf @method('DELETE')
                <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
              </form>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  @endif

  {{-- Pangkat & Golongan --}}
  <div class="mt-10">
    @include('employees._section', [
      'title' => 'Pangkat & Golongan',
      'createUrl' => $admin ? route('sub.create', [$e, 'pangkat']) : null,
      'createLabel' => 'Tambah',
    ])
    @if($e->golonganAkhir)
      <div class="mb-5 p-4 rounded-xl" style="background:var(--blue-50); border:1px solid var(--blue-100)">
        <div class="text-[11px] font-semibold" style="color:var(--ink-500)">GOLONGAN SEKARANG</div>
        <div class="text-[20px] font-bold" style="color:var(--blue-600)">{{ $e->golonganAkhir->golongan }}</div>
        <div class="text-[12px]" style="color:var(--ink-500)">{{ $e->golonganAkhir->pangkat ?? '' }}@if($e->tmt_golongan_akhir) · TMT {{ $e->tmt_golongan_akhir->format('d M Y') }}@endif</div>
      </div>
    @endif
    @if($e->rankHistories->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada riwayat pangkat.</p></div>
    @else
      <div class="timeline">
        @foreach ($e->rankHistories->sortByDesc('tmt') as $rh)
          <div class="tl-item">
            <span class="tl-dot"></span>
            <div class="tl-year">{{ optional($rh->tmt)->format('Y') }}</div>
            <div class="text-[14px] font-semibold mt-0.5">{{ $rh->rank->golongan ?? '' }}</div>
            <div class="text-[12px]" style="color:var(--ink-500)">
              {{ $rh->rank->pangkat ?? '' }} · TMT {{ optional($rh->tmt)->format('d M Y') ?? '' }}
              @if($rh->no_sk)<div class="mt-0.5">SK: {{ $rh->no_sk }}</div>@endif
            </div>
            <div class="flex gap-1.5 mt-2 text-[11.5px]">
              @if($rh->file_sk_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['pangkat', $rh->id]) }}">Unduh SK</a>@endif
              @if($admin)
                <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'pangkat', $rh->id]) }}">Edit</a>
                <form method="POST" action="{{ route('sub.destroy', [$e, 'pangkat', $rh->id]) }}" onsubmit="return confirm('Hapus riwayat pangkat ini?')">
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

  {{-- Riwayat Mutasi --}}
  <div class="mt-10">
    @include('employees._section', [
      'title' => 'Riwayat Mutasi',
      'createUrl' => $admin ? route('sub.create', [$e, 'mutasi']) : null,
      'createLabel' => 'Tambah',
    ])
    @if($e->mutations->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada riwayat mutasi.</p></div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach ($e->mutations->sortByDesc('tanggal_mutasi') as $m)
          <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
            <div class="flex items-center justify-between gap-2">
              <span class="badge" style="background:var(--blue-50); color:var(--blue-600); border-color:var(--blue-100)">{{ $m->jenis_mutasi }}</span>
              <div class="text-[11px]" style="color:var(--ink-300)">{{ optional($m->tanggal_mutasi)->format('d M Y') }}</div>
            </div>
            <div class="text-[13.5px] font-semibold mt-2">
              {{ $m->jabatan_lama ?: ($m->unitAsal?->name ?? '') }} → {{ $m->jabatan_baru ?: ($m->unitTujuan?->name ?? '') }}
            </div>
            @if($m->no_sk)<div class="text-[11.5px] mt-1" style="color:var(--ink-500)">SK: {{ $m->no_sk }}</div>@endif
            <div class="flex gap-2 mt-2 text-[11.5px]">
              @if($m->file_sk_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['mutasi', $m->id]) }}">Unduh SK</a>@endif
              @if($admin)
                <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'mutasi', $m->id]) }}">Edit</a>
                <form method="POST" action="{{ route('sub.destroy', [$e, 'mutasi', $m->id]) }}" onsubmit="return confirm('Hapus riwayat mutasi ini?')">
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

  {{-- KGB --}}
  <div class="mt-10">
    @include('employees._section', [
      'title' => 'Riwayat KGB (Gaji Berkala)',
      'createUrl' => $admin ? route('sub.create', [$e, 'kgb']) : null,
      'createLabel' => 'Tambah',
    ])
    @if($e->salaryHistories->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada riwayat KGB.</p></div>
    @else
      <div class="table-wrap">
        <table class="w-full min-w-[720px]">
          <thead><tr>
            <th class="table-th">Golongan</th><th class="table-th">Gaji Pokok</th><th class="table-th">No. SK</th>
            <th class="table-th">Tanggal SK</th><th class="table-th">TMT</th><th class="table-th">Masa Kerja</th><th class="table-th text-right">Aksi</th>
          </tr></thead>
          <tbody>
            @foreach ($e->salaryHistories->sortByDesc('tanggal_sk') as $s)
              <tr class="row-line">
                <td class="table-td font-medium">{{ $s->rank->golongan ?? '' }}</td>
                <td class="table-td">Rp {{ number_format($s->gaji_pokok ?? 0, 0, ',', '.') }}</td>
                <td class="table-td">{{ $s->no_sk ?? '' }}</td>
                <td class="table-td">{{ optional($s->tanggal_sk)->format('d M Y') ?? '' }}</td>
                <td class="table-td">{{ optional($s->tmt)->format('d M Y') ?? '' }}</td>
                <td class="table-td">{{ trim(($s->masa_kerja_tahun ?? '' ? $s->masa_kerja_tahun . ' th' : '') . ' ' . ($s->masa_kerja_bulan ?? '' ? $s->masa_kerja_bulan . ' bln' : '')) }}</td>
                <td class="table-td text-right">
                  <div class="flex justify-end gap-2 text-[11.5px]">
                    @if($s->file_sk_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['kgb', $s->id]) }}">Unduh</a>@endif
                    @if($admin)
                      <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'kgb', $s->id]) }}">Edit</a>
                      <form method="POST" action="{{ route('sub.destroy', [$e, 'kgb', $s->id]) }}" onsubmit="return confirm('Hapus riwayat KGB ini?')">
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

  {{-- PMK --}}
  <div class="mt-10">
    @include('employees._section', [
      'title' => 'Peninjauan Masa Kerja (PMK)',
      'createUrl' => $admin ? route('sub.create', [$e, 'pmk']) : null,
      'createLabel' => 'Tambah',
    ])
    @if($e->pmkHistories->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada riwayat PMK.</p></div>
    @else
      <div class="table-wrap">
        <table class="w-full min-w-[680px]">
          <thead><tr>
            <th class="table-th">No. SK</th><th class="table-th">Tanggal SK</th><th class="table-th">TMT</th>
            <th class="table-th">Tambahan</th><th class="table-th">Keterangan</th><th class="table-th text-right">Aksi</th>
          </tr></thead>
          <tbody>
            @foreach ($e->pmkHistories->sortByDesc('tanggal_sk') as $pm)
              <tr class="row-line">
                <td class="table-td font-medium">{{ $pm->no_sk ?? '' }}</td>
                <td class="table-td">{{ optional($pm->tanggal_sk)->format('d M Y') ?? '' }}</td>
                <td class="table-td">{{ optional($pm->tmt)->format('d M Y') ?? '' }}</td>
                <td class="table-td">{{ trim(($pm->tambah_tahun ? $pm->tambah_tahun . ' th' : '') . ' ' . ($pm->tambah_bulan ? $pm->tambah_bulan . ' bln' : '')) }}</td>
                <td class="table-td">{{ $pm->keterangan ?? '' }}</td>
                <td class="table-td text-right">
                  <div class="flex justify-end gap-2 text-[11.5px]">
                    @if($pm->file_sk_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['pmk', $pm->id]) }}">Unduh</a>@endif
                    @if($admin)
                      <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'pmk', $pm->id]) }}">Edit</a>
                      <form method="POST" action="{{ route('sub.destroy', [$e, 'pmk', $pm->id]) }}" onsubmit="return confirm('Hapus riwayat PMK ini?')">
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

  {{-- Riwayat Cuti --}}
  <div class="mt-10">
    @include('employees._section', [
      'title' => 'Riwayat Cuti',
      'createUrl' => $admin ? route('sub.create', [$e, 'cuti']) : null,
      'createLabel' => 'Tambah',
    ])
    @if($e->leaves->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada riwayat cuti.</p></div>
    @else
      <div class="table-wrap">
        <table class="w-full min-w-[680px]">
          <thead><tr>
            <th class="table-th">Jenis Cuti</th><th class="table-th">Tanggal Mulai</th><th class="table-th">Tanggal Selesai</th>
            <th class="table-th">Hari</th><th class="table-th">No. SK</th><th class="table-th text-right">Aksi</th>
          </tr></thead>
          <tbody>
            @foreach ($e->leaves->sortByDesc('tanggal_mulai') as $lv)
              <tr class="row-line">
                <td class="table-td font-medium">{{ $lv->jenis_cuti }}</td>
                <td class="table-td">{{ optional($lv->tanggal_mulai)->format('d M Y') ?? '' }}</td>
                <td class="table-td">{{ optional($lv->tanggal_selesai)->format('d M Y') ?? '' }}</td>
                <td class="table-td">{{ $lv->jumlah_hari ?? '' }}</td>
                <td class="table-td">{{ $lv->no_sk ?? '' }}</td>
                <td class="table-td text-right">
                  <div class="flex justify-end gap-2 text-[11.5px]">
                    @if($lv->file_sk_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['cuti', $lv->id]) }}">Unduh</a>@endif
                    @if($admin)
                      <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'cuti', $lv->id]) }}">Edit</a>
                      <form method="POST" action="{{ route('sub.destroy', [$e, 'cuti', $lv->id]) }}" onsubmit="return confirm('Hapus riwayat cuti ini?')">
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

  {{-- Riwayat Inaktif --}}
  <div class="mt-10">
    @include('employees._section', [
      'title' => 'Riwayat Inaktif',
      'createUrl' => $admin ? route('sub.create', [$e, 'inaktif']) : null,
      'createLabel' => 'Tambah',
    ])
    @if($e->inactivePeriods->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada riwayat inaktif.</p></div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach ($e->inactivePeriods->sortByDesc('tanggal_mulai') as $ia)
          <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
            <div class="flex items-center justify-between gap-2">
              <span class="badge" style="background:var(--amber-50); color:var(--amber-600); border-color:var(--amber-100)">{{ $ia->status }}</span>
              <div class="text-[11px]" style="color:var(--ink-300)">{{ optional($ia->tanggal_mulai)->format('d M Y') }} – {{ optional($ia->tanggal_selesai)->format('d M Y') }}</div>
            </div>
            @if($ia->alasan)<div class="text-[12px] mt-2" style="color:var(--ink-700)">{{ $ia->alasan }}</div>@endif
            @if($ia->no_sk)<div class="text-[11.5px] mt-1" style="color:var(--ink-500)">SK: {{ $ia->no_sk }}</div>@endif
            <div class="flex gap-2 mt-2 text-[11.5px]">
              @if($admin)
                <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'inaktif', $ia->id]) }}">Edit</a>
                <form method="POST" action="{{ route('sub.destroy', [$e, 'inaktif', $ia->id]) }}" onsubmit="return confirm('Hapus riwayat inaktif ini?')">
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

  {{-- Riwayat Kontrak PPPK --}}
  <div class="mt-10">
    @include('employees._section', [
      'title' => 'Riwayat Kontrak PPPK',
      'createUrl' => $admin ? route('sub.create', [$e, 'kontrak_pppk']) : null,
      'createLabel' => 'Tambah',
    ])
    @if($e->pppkContracts->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada riwayat kontrak PPPK.</p></div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach ($e->pppkContracts->sortByDesc('tanggal_mulai') as $pp)
          <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
            <div class="flex items-center justify-between gap-2">
              <div class="text-[13.5px] font-semibold">{{ $pp->nomor_kontrak }}</div>
              <span class="badge" style="background:var(--teal-50); color:var(--teal-600); border-color:var(--teal-100)">{{ $pp->masa_kerja ?? 'Kontrak' }}</span>
            </div>
            <div class="text-[12px] mt-1.5" style="color:var(--ink-500)">
              {{ optional($pp->tanggal_mulai)->format('d M Y') }} – {{ optional($pp->tanggal_selesai)->format('d M Y') }}
              @if($pp->instansi)<div class="mt-0.5">Instansi: {{ $pp->instansi }}</div>@endif
            </div>
            <div class="flex gap-2 mt-2 text-[11.5px]">
              @if($pp->file_kontrak_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['kontrak_pppk', $pp->id]) }}">Unduh Kontrak</a>@endif
              @if($admin)
                <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'kontrak_pppk', $pp->id]) }}">Edit</a>
                <form method="POST" action="{{ route('sub.destroy', [$e, 'kontrak_pppk', $pp->id]) }}" onsubmit="return confirm('Hapus riwayat kontrak ini?')">
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

  {{-- Kontak Darurat --}}
  <div class="mt-10">
    @include('employees._section', [
      'title' => 'Kontak Darurat',
      'createUrl' => $admin ? route('sub.create', [$e, 'kontak_darurat']) : null,
      'createLabel' => 'Tambah',
    ])
    @if($e->emergencyContacts->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada kontak darurat.</p></div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach ($e->emergencyContacts as $ec)
          <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
            <div class="flex items-start justify-between gap-2">
              <div>
                <div class="text-[13.5px] font-semibold">{{ $ec->nama }}</div>
                <div class="text-[12px]" style="color:var(--ink-500)">{{ $ec->hubungan ?? '' }}</div>
              </div>
              <div class="flex gap-2 text-[11.5px]">
                @if($admin)
                  <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'kontak_darurat', $ec->id]) }}">Edit</a>
                  <form method="POST" action="{{ route('sub.destroy', [$e, 'kontak_darurat', $ec->id]) }}" onsubmit="return confirm('Hapus kontak darurat ini?')">
                    @csrf @method('DELETE')
                    <button class="font-semibold" style="color:var(--red-600)">Hapus</button>
                  </form>
                @endif
              </div>
            </div>
            @if($ec->telepon)<div class="mt-2 text-[12.5px] font-semibold" style="color:var(--blue-600)">{{ $ec->telepon }}</div>@endif
            @if($ec->alamat)<div class="text-[11.5px] mt-1" style="color:var(--ink-500)">{{ $ec->alamat }}</div>@endif
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Kedudukan Hukum --}}
  <div class="mt-10">
    @include('employees._section', [
      'title' => 'Kedudukan Hukum',
      'createUrl' => $admin ? route('sub.create', [$e, 'kedudukan_hukum']) : null,
      'createLabel' => 'Tambah',
    ])
    @if($e->legalStatuses->isEmpty())
      <div class="empty-state"><p class="text-[13px]">Belum ada catatan kedudukan hukum.</p></div>
    @else
      <div class="table-wrap">
        <table class="w-full min-w-[680px]">
          <thead><tr>
            <th class="table-th">Status</th><th class="table-th">Kasus</th><th class="table-th">Tanggal</th>
            <th class="table-th">No. Putusan</th><th class="table-th text-right">Aksi</th>
          </tr></thead>
          <tbody>
            @foreach ($e->legalStatuses as $ls)
              <tr class="row-line">
                <td class="table-td font-medium">{{ $ls->status }}</td>
                <td class="table-td">{{ $ls->kasus ?? '' }}</td>
                <td class="table-td">{{ optional($ls->tanggal)->format('d M Y') ?? '' }}</td>
                <td class="table-td">{{ $ls->no_putusan ?? '' }}</td>
                <td class="table-td text-right">
                  <div class="flex justify-end gap-2 text-[11.5px]">
                    @if($ls->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['kedudukan_hukum', $ls->id]) }}">Unduh</a>@endif
                    @if($admin)
                      <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'kedudukan_hukum', $ls->id]) }}">Edit</a>
                      <form method="POST" action="{{ route('sub.destroy', [$e, 'kedudukan_hukum', $ls->id]) }}" onsubmit="return confirm('Hapus catatan ini?')">
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

  {{-- Hukdis --}}
  @if($sensitive)
    <div class="mt-10">
      @include('employees._section', [
        'title' => 'Catatan Hukuman Disiplin',
        'sub' => 'Khusus Admin/Super Admin.',
        'createUrl' => $admin ? route('sub.create', [$e, 'hukdis']) : null,
        'createLabel' => 'Tambah',
      ])
      @if($e->disciplines->isEmpty())
        <div class="empty-state"><p class="text-[13px]">Tidak ada catatan pelanggaran / disiplin.</p></div>
      @else
        <div class="table-wrap">
          <table class="w-full min-w-[680px]">
            <thead><tr><th class="table-th">Jenis Pelanggaran</th><th class="table-th">Tanggal</th><th class="table-th">Tingkat</th><th class="table-th">Sanksi</th><th class="table-th">No. Keputusan</th><th class="table-th text-right">Aksi</th></tr></thead>
            <tbody>
              @foreach ($e->disciplines as $d)
                <tr class="row-line">
                  <td class="table-td font-medium">{{ $d->jenis_pelanggaran }}</td>
                  <td class="table-td">{{ optional($d->tanggal)->format('d M Y') ?? '' }}</td>
                  <td class="table-td">{{ $d->tingkat_pelanggaran ?? '' }}</td>
                  <td class="table-td">{{ $d->sanksi ?? '' }}</td>
                  <td class="table-td">{{ $d->no_keputusan ?? '' }}</td>
                  <td class="table-td text-right">
                    <div class="flex justify-end gap-2 text-[11.5px]">
                      @if($d->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['hukdis', $d->id]) }}">Unduh</a>@endif
                      @if($admin)
                        <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'hukdis', $d->id]) }}">Edit</a>
                        <form method="POST" action="{{ route('sub.destroy', [$e, 'hukdis', $d->id]) }}" onsubmit="return confirm('Hapus catatan hukdis ini?')">
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
  @endif

  {{-- Riwayat Penyakit --}}
  @if($sensitive)
    <div class="mt-10">
      @include('employees._section', [
        'title' => 'Riwayat Penyakit',
        'sub' => 'Khusus Admin/Super Admin.',
        'createUrl' => $admin ? route('sub.create', [$e, 'penyakit']) : null,
        'createLabel' => 'Tambah',
      ])
      @if($e->diseases->isEmpty())
        <div class="empty-state"><p class="text-[13px]">Belum ada riwayat penyakit.</p></div>
      @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          @foreach ($e->diseases->sortByDesc('tanggal') as $dz)
            <div class="p-4 rounded-xl card-hover" style="border:1px solid var(--line-soft)">
              <div class="text-[13.5px] font-semibold">{{ $dz->nama_penyakit }}</div>
              <div class="text-[11.5px] mt-0.5" style="color:var(--ink-500)">{{ optional($dz->tanggal)->format('d M Y') ?? '' }}</div>
              @if($dz->keterangan)<div class="text-[11.5px] mt-2" style="color:var(--ink-700)">{{ $dz->keterangan }}</div>@endif
              <div class="flex gap-2 mt-2 text-[11.5px]">
                @if($dz->file_path)<a class="font-semibold" style="color:var(--blue-600)" href="{{ route('sub.download', ['penyakit', $dz->id]) }}">Unduh</a>@endif
                @if($admin)
                  <a class="font-semibold" style="color:var(--ink-500)" href="{{ route('sub.edit', [$e, 'penyakit', $dz->id]) }}">Edit</a>
                  <form method="POST" action="{{ route('sub.destroy', [$e, 'penyakit', $dz->id]) }}" onsubmit="return confirm('Hapus riwayat penyakit ini?')">
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
  @endif
</div>