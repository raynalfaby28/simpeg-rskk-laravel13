{{-- Compact status pill/badge (generat table status) --}}
@props([
  'status' => 'pending',      // pending | reviewed | approved | rejected | aktif | nonaktif | dsb
  'label'  => null,
  'dot'    => true,
  'class'  => null,
])
@php
  $map = [
    'approved'  => ['bg'=>'var(--green-50)',  'fg'=>'var(--green-600)', 'bd'=>'var(--green-100)', 'tx'=>'Disetujui'],
    'rejected'  => ['bg'=>'var(--red-50)',    'fg'=>'var(--red-600)',   'bd'=>'var(--red-100)',   'tx'=>'Ditolak'],
    'reviewed'  => ['bg'=>'var(--blue-50)',   'fg'=>'var(--blue-600)',  'bd'=>'var(--blue-100)',  'tx'=>'Ditinjau'],
    'pending'   => ['bg'=>'var(--amber-50)',  'fg'=>'var(--amber-600)', 'bd'=>'var(--amber-100)', 'tx'=>'Menunggu'],
    'aktif'     => ['bg'=>'var(--green-50)',  'fg'=>'var(--green-600)', 'bd'=>'var(--green-100)', 'tx'=>'Aktif'],
    'nonaktif'  => ['bg'=>'var(--red-50)',    'fg'=>'var(--red-600)',   'bd'=>'var(--red-100)',   'tx'=>'Nonaktif'],
  ];
  $c = $map[strtolower($status)] ?? ['bg'=>'var(--ink-50)','fg'=>'var(--ink-600)','bd'=>'var(--ink-100)','tx'=>ucfirst($status)];
  $pill = $label ?? $c['tx'];
@endphp
<span class="badge {{ $class }}" style="background:{{ $c['bg'] }}; color:{{ $c['fg'] }}; border-color:{{ $c['bd'] }}" {{ $attributes }}>
  @if($dot)<span class="dot" style="background:{{ $c['fg'] }}"></span>@endif
  {{ $pill }}
</span>
