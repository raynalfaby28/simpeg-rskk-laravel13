@extends('layouts.app')
@section('title', 'Detail Pengajuan')
@section('nav-approvals', 'active')
@section('crumb', 'Approval Center')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Detail Pengajuan</h1>
    <p class="page-desc">Tinjau perbandingan data sebelum diproses.</p>
  </div>
  <a href="{{ route('approvals.index') }}" class="btn btn-outline btn-sm">&larr; Kembali ke Approval Center</a>
</div>

<div class="card p-6 mb-5">
  <div class="flex items-center gap-4 flex-wrap">
    <img src="https://ui-avatars.com/api/?name={{ urlencode($request->employee->nama_lengkap) }}&background=2563EB&color=fff&size=96" class="avatar w-12 h-12">
    <div class="min-w-0">
      <div class="font-semibold text-[16px]">{{ $request->employee->nama_lengkap }}</div>
      <div class="text-[12.5px]" style="color:var(--ink-500)">
        Mengajukan perubahan modul <b class="capitalize">{{ ucfirst($request->module_type) }}</b> pada {{ $request->created_at->format('d M Y, H:i') }}
      </div>
    </div>
  </div>
</div>

<div class="card p-6 mb-5">
  <h3 class="text-[11px] font-bold tracking-wider mb-4" style="color:var(--blue-600); text-transform:uppercase">Perbandingan Data</h3>
  <div class="overflow-x-auto">
    <table class="w-full text-[13.5px] min-w-[560px]">
      <thead>
        <tr>
          <th class="table-th" style="text-align:left">Field</th>
          <th class="table-th" style="text-align:left">Data Sebelum</th>
          <th class="table-th" style="text-align:left">Data Sesudah</th>
        </tr>
      </thead>
      <tbody>
        @foreach($request->new_data as $field => $newValue)
          @php $oldValue = $request->old_data[$field] ?? null; $changed = $oldValue != $newValue; @endphp
          <tr class="row-line">
            <td class="table-td font-medium capitalize">{{ str_replace('_', ' ', $field) }}</td>
            <td class="table-td">{{ $oldValue ?: '—' }}</td>
            <td class="table-td font-semibold" style="{{ $changed ? 'background:var(--amber-50); color:var(--amber-600); border-left:3px solid var(--amber-600)' : 'color:var(--ink-700)' }}">
              {{ $newValue ?: '—' }}
              @if($changed)<span class="badge ml-2" style="background:var(--amber-100); color:var(--amber-600); border-color:var(--amber-600)">Berubah</span>@endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<div class="flex gap-2 mb-8">
  <form method="POST" action="{{ route('approvals.approve', $request) }}" id="form-approve">
    @csrf
    <button type="submit" id="btn-approve" class="btn btn-primary inline-flex items-center gap-1.5">
      <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      Setujui
    </button>
  </form>

  <button type="button" onclick="document.getElementById('reject-form').classList.toggle('hidden')"
    class="btn btn-outline inline-flex items-center gap-1.5" style="color:var(--red-600)">
    <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    Tolak
  </button>
</div>

<div id="reject-form" class="hidden card p-6 mb-8">
  <form method="POST" action="{{ route('approvals.reject', $request) }}" id="form-reject">
    @csrf
    <label class="flabel">Alasan Penolakan <span class="req">*</span></label>
    <textarea name="rejection_reason" rows="3" required
      class="input mb-3"
      placeholder="Contoh: Dokumen pendukung belum dilampirkan"></textarea>
    <button type="submit" id="btn-reject" class="btn btn-danger">Kirim Penolakan</button>
  </form>
</div>

<script>
  document.getElementById('form-approve').addEventListener('submit', function (e) {
    const btn = document.getElementById('btn-approve');
    btn.disabled = true;
    btn.style.opacity = .7;
    btn.textContent = 'Memproses…';
    setTimeout(() => this.submit(), 500);
    e.preventDefault();
  });
  document.getElementById('form-reject').addEventListener('submit', function (e) {
    const btn = document.getElementById('btn-reject');
    btn.disabled = true;
    btn.style.opacity = .7;
    btn.textContent = 'Mengirim…';
    setTimeout(() => this.submit(), 500);
    e.preventDefault();
  });
</script>
@endsection