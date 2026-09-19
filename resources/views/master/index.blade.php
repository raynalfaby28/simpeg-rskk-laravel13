@extends('layouts.app')
@section('title', $conf['label'])
@section('nav-master', 'active')
@section('crumb', 'Master Data')

@section('content')
<div class="flex flex-col lg:flex-row gap-5">

  {{-- Sidebar daftar jenis referensi --}}
  <aside class="card p-3 lg:w-72 lg:shrink-0 self-start w-full overflow-hidden" style="position:sticky; top:88px">
    <div class="px-2 pb-2 pt-1">
      <p class="text-[12px] font-semibold uppercase tracking-wide" style="color:var(--ink-700)">Kelola Referensi</p>
      <p class="text-[11.5px] mt-0.5" style="color:var(--ink-400)">Pilih jenis data master untuk dikelola.</p>
    </div>
    <nav class="flex flex-col gap-1">
      <a href="{{ route('master.types.index') }}" class="flex items-center justify-center gap-1.5 px-2.5 py-1.5 mb-1 rounded-lg text-[12px] font-semibold transition"
        style="border:1px dashed var(--blue-300); color:var(--blue-600); background:var(--blue-50, #EFF6FF)">
        <svg style="width:13px;height:13px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Tambah Jenis Master Data
      </a>
      @foreach ($types as $key => $c)
        <a href="{{ route('master.index', $key) }}"
          class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[12.5px] font-medium transition"
          style="{{ $key === $type ? 'background:var(--blue-600); color:#fff' : 'color:var(--ink-600); border:1px solid var(--line); background:#fff' }}">
          <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $key === $type ? '#fff' : 'var(--blue-300, #93b4f0)' }}"></span>
          <span class="flex-1 min-w-0">{{ $c['label'] }}</span>
          <span class="text-[10.5px] font-semibold px-1.5 py-0.5 rounded-full"
            style="{{ $key === $type ? 'background:rgba(255,255,255,0.22); color:#fff' : 'background:var(--navy-50, #EEF1F6); color:var(--ink-500)' }}">{{ $typeCounts[$key] ?? 0 }}</span>
        </a>
      @endforeach
    </nav>
    <div class="mt-3 px-2.5 pt-3 border-t" style="border-color:var(--line)">
      <p class="text-[11px] leading-relaxed" style="color:var(--ink-400)">Referensi yang tampil di dropdown formulir kepegawaian. Admin &amp; Super Admin dapat menambah jenis baru atau mengelola isinya.</p>
    </div>
  </aside>

  {{-- Konten aktif --}}
  <div class="flex-1 min-w-0">
    <div class="page-head">
      <div>
        <h1 class="page-title">{{ $conf['label'] }}</h1>
        <p class="page-desc">Kelola referensi data yang dipakai modul kepegawaian.</p>
      </div>
      <a href="{{ route('master.create', $type) }}" class="btn btn-primary">+ Tambah {{ $conf['label'] }}</a>
    </div>

    @if(session('error'))
      <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <div class="card overflow-hidden">
      @if($rows->isEmpty())
        <div class="p-10 text-center text-sm" style="color:var(--ink-500)">
          Belum ada data {{ strtolower($conf['label']) }}.
        </div>
      @else
        <div class="table-wrap">
          <table class="w-full text-sm">
            <thead>
              <tr class="table-th">
                <th class="px-4 py-3 text-left font-medium">#</th>
                @foreach ($conf['columns'] as $key => $label)
                  <th class="px-4 py-3 text-left font-medium">{{ $label }}</th>
                @endforeach
                <th class="px-4 py-3 text-right font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($rows as $i => $row)
                <tr class="row-line">
                  <td class="table-td px-4 py-3" style="color:var(--ink-500)">{{ $i + 1 }}</td>
                  @foreach ($conf['columns'] as $key => $label)
                    <td class="table-td px-4 py-3" style="color:var(--ink-700)">
                      @isset($conf['display'][$key])
                        {{ $conf['display'][$key]($row) }}
                      @elseif(strtolower($key) === 'type' && isset($conf['columns_label']))
                        @php $v = $row->{$key}; @endphp
                        <span class="badge" style="background:var(--teal-50); color:var(--teal-700); border-color:var(--teal-100)">
                          {{ $conf['columns_label'][$v] ?? $v }}
                        </span>
                      @else
                        {{ $row->{$key} ?? '—' }}
                      @endisset
                    </td>
                  @endforeach
                  <td class="table-td px-4 py-3 text-right whitespace-nowrap">
                    <a href="{{ route('master.edit', [$type, $row->id]) }}" class="btn btn-outline btn-sm mr-1">Edit</a>
                    <form method="POST" action="{{ route('master.destroy', [$type, $row->id]) }}" class="inline"
                      onsubmit="return confirm('Yakin hapus {{ $conf['label'] }} ini?')">
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
  </div>

</div>
@endsection