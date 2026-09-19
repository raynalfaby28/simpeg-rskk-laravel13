@extends('layouts.app')
@section('title', 'Manajemen Akun')
@section('nav-accounts', 'active')
@section('crumb', 'Administrasi')

@section('content')
<div class="page-head">
  <div>
    <h1 class="page-title">Manajemen Akun</h1>
    <p class="page-desc">Kelola akun login pegawai untuk aplikasi.</p>
  </div>
  <a href="{{ route('admin.accounts.create') }}" class="btn-primary px-4 py-2 rounded-lg text-sm font-medium">+ Tambah Akun</a>
</div>

<form method="GET" class="mb-4">
  <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari NIP / nama..." class="input" style="width:18rem">
</form>

<div class="card overflow-hidden">
  <div class="table-wrap">
  <table class="w-full text-[13.5px] min-w-[900px]">
    <thead>
      <tr>
        <th class="table-th">NIP</th>
        <th class="table-th">Nama</th>
        @if(auth()->user()->role === 'super_admin')
        <th class="table-th">Password</th>
        @endif
        <th class="table-th">Role</th>
        <th class="table-th">Status</th>
        <th class="table-th">Login Terakhir</th>
        <th class="table-th"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($accounts as $account)
      <tr class="row-line">
        <td class="table-td">{{ $account->nip }}</td>
        <td class="table-td font-medium">{{ $account->name }}</td>
        @if(auth()->user()->role === 'super_admin')
        <td class="table-td">
          @if($account->password_cipher)
            <div class="flex items-center gap-1.5" style="min-width:190px">
              <span data-pw-display="{{ $account->id }}" class="font-mono text-[12.5px] tracking-wider" style="color:var(--ink-500)">••••••••</span>
              <button type="button" data-pw-toggle="{{ $account->id }}" data-pw-url="{{ route('admin.accounts.password', $account) }}"
                class="pw-eye flex items-center justify-center w-7 h-7 rounded-md transition" style="color:var(--ink-400); border:1px solid var(--line)"
                title="Lihat password" aria-label="Lihat password">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
              <button type="button" data-pw-copy="{{ $account->id }}" style="display:none; color:var(--blue-600); border:1px solid var(--blue-100)"
                class="pw-copy flex items-center justify-center w-7 h-7 rounded-md transition"
                title="Salin password" aria-label="Salin password">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              </button>
            </div>
          @else
            <span style="color:var(--ink-300)" title="Password lama belum tersimpan. Reset password lalu lihat kembali.">—</span>
          @endif
        </td>
        @endif
        <td class="table-td">
          @php
            $rolePill = match ($account->role) {
              'super_admin' => ['bg' => 'var(--navy-50,#EEF1F6)', 'fg' => 'var(--navy-800)', 'bd' => 'var(--line)', 'txt' => 'Super Admin'],
              'admin' => ['bg' => 'var(--blue-50)', 'fg' => 'var(--blue-600)', 'bd' => 'var(--blue-100)', 'txt' => 'Admin'],
              default => ['bg' => 'var(--teal-50)', 'fg' => 'var(--teal-700)', 'bd' => 'var(--teal-100)', 'txt' => 'Pegawai'],
            };
          @endphp
          @if(auth()->user()->role === 'super_admin' && $account->role !== 'super_admin')
            <form method="POST" action="{{ route('admin.accounts.update-role', $account) }}">
              @csrf @method('PATCH')
              <select name="role" onchange="this.form.submit()" title="Ubah role (langsung tersimpan)"
                class="px-2 py-1 rounded-md text-[11.5px] font-semibold cursor-pointer"
                style="background:{{ $rolePill['bg'] }}; color:{{ $rolePill['fg'] }}; border:1px solid {{ $rolePill['bd'] }}">
                <option value="admin" @selected($account->role === 'admin')>Admin</option>
                <option value="user" @selected($account->role === 'user')>Pegawai</option>
              </select>
            </form>
          @else
            <span class="badge" style="background:{{ $rolePill['bg'] }}; color:{{ $rolePill['fg'] }}; border-color:{{ $rolePill['bd'] }}">
              <span class="dot" style="background:{{ $rolePill['fg'] }}"></span>{{ $rolePill['txt'] }}
            </span>
          @endif
        </td>
        <td class="table-td">
          @if($account->is_active)
            <span class="badge" style="background:var(--teal-50); color:var(--teal-700); border-color:var(--teal-100)">Aktif</span>
          @else
            <span class="badge" style="background:#F3F2ED; color:var(--ink-500); border-color:var(--line)">Nonaktif</span>
          @endif
        </td>
        <td class="table-td" style="color:var(--ink-500)">
          {{ $account->last_login_at?->diffForHumans() ?? 'Belum pernah' }}
        </td>
        <td class="table-td text-right">
          <div class="flex justify-end gap-1.5">
            @if($account->id !== auth()->user()->id)
              <form method="POST" action="{{ route('admin.accounts.toggle-active', $account) }}">
                @csrf @method('PATCH')
                <button class="btn-outline px-2.5 py-1 rounded text-[11.5px]">
                  {{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </form>
            @else
              <span class="px-2.5 py-1 rounded text-[11.5px]" style="color:var(--ink-300)">Akun Anda</span>
            @endif
            <form method="POST" action="{{ route('admin.accounts.reset-password', $account) }}" onsubmit="return confirm('Reset password akun ini?')">
              @csrf @method('PATCH')
              <button class="btn-outline px-2.5 py-1 rounded text-[11.5px]">Reset Password</button>
            </form>
            @if(auth()->user()->role === 'super_admin' && $account->role !== 'super_admin')
            <form method="POST" action="{{ route('admin.accounts.destroy', $account) }}" onsubmit="return confirm('Hapus akun ini?')">
              @csrf @method('DELETE')
              <button class="px-2.5 py-1 rounded text-[11.5px]" style="border:1px solid var(--red-100); color:var(--red-600)">Hapus</button>
            </form>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="{{ auth()->user()->role === 'super_admin' ? 7 : 6 }}" class="table-td text-center py-8" style="color:var(--ink-500)">Belum ada akun.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div>

<div class="mt-4">{{ $accounts->links() }}</div>

@push('scripts')
<script>
document.addEventListener('click', async function (e) {
  var eye = e.target.closest('[data-pw-toggle]');
  var copy = e.target.closest('[data-pw-copy]');
  if (!eye && !copy) return;

  var id = (eye || copy).getAttribute(eye ? 'data-pw-toggle' : 'data-pw-copy');
  var display = document.querySelector('[data-pw-display="' + id + '"]');
  var copyBtn = document.querySelector('[data-pw-copy="' + id + '"]');
  if (!display) return;

  /* Tombol salin */
  if (copy) {
    var pw = copyBtn && copyBtn.dataset.pw;
    if (!pw) return;
    try {
      await navigator.clipboard.writeText(pw);
      copyBtn.style.color = 'var(--green-600)';
      setTimeout(function(){ copyBtn.style.color = 'var(--blue-600)'; }, 1200);
      if (window.toast) window.toast('Password disalin ke clipboard.', 'success');
    } catch (err) {
      if (window.toast) window.toast('Gagal menyalin password.', 'error');
    }
    return;
  }

  /* Tombol lihat / sembunyikan (eye) */
  var eyeBtn = eye;
  if (eyeBtn.dataset.revealed === '1') {
    display.textContent = '••••••••';
    display.style.color = 'var(--ink-500)';
    if (copyBtn) copyBtn.style.display = 'none';
    eyeBtn.dataset.revealed = '0';
    eyeBtn.style.color = 'var(--ink-400)';
    eyeBtn.title = 'Lihat password';
    return;
  }

  eyeBtn.disabled = true;
  try {
    var res = await fetch(eyeBtn.getAttribute('data-pw-url'), { headers: { 'Accept': 'application/json' } });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    var data = await res.json();
    var pw = data.password || '';
    if (!pw) throw new Error('empty');
    display.textContent = pw;
    display.style.color = 'var(--ink-900, #0f172a)';
    if (copyBtn) { copyBtn.dataset.pw = pw; copyBtn.style.display = 'flex'; }
    eyeBtn.dataset.revealed = '1';
    eyeBtn.style.color = 'var(--blue-600)';
    eyeBtn.title = 'Sembunyikan password';
  } catch (err) {
    if (window.toast) {
      window.toast('Gagal mengambil password akun.', 'error');
    } else {
      alert('Gagal mengambil password akun.');
    }
  } finally {
    eyeBtn.disabled = false;
  }
});
</script>
@endpush
@endsection
