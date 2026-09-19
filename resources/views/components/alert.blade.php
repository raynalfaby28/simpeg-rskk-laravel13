{{-- Enterprise flash/compact inline alert  --}}
@props([
  'type' => 'success',     // success | error | warning | info
  'title' => null,
  'dismissible' => true,
  'class' => null,
])
@php
  $map = [
    'success' => ['cls' => 'alert-success', 'ic' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    'error'   => ['cls' => 'alert-danger',  'ic' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    'warning' => ['cls' => 'alert-warning', 'ic' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.01M9.6 3.75l-5.4 9.3A2 2 0 005.9 17h12.2a2 2 0 001.7-2.95l-5.4-9.3A2 2 0 009.6 3.75z"/>'],
    'info'    => ['cls' => 'alert-info',    'ic' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
  ];
  $cfg = $map[$type] ?? $map['info'];
@endphp
<div class="alert {{ $cfg['cls'] }} {{ $class }}" role="{{ $type === 'error' ? 'alert' : 'status' }}" {{ $attributes }}>
  <svg style="width:16px;height:16px;flex:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">{!! $cfg['ic'] !!}</svg>
  <div style="min-width:0">
    @if($title)<div class="font-semibold text-[13px]">{{ $title }}</div>@endif
    <div style="min-width:0">{{ $slot }}</div>
  </div>
  @if($dismissible)
    <button type="button" class="ml-auto flex-none" style="margin-left:auto; padding:4px; border:0; background:none; cursor:pointer; color:var(--ink-400)" onclick="this.closest('.alert').style.display='none'" aria-label="Tutup">
      <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  @endif
</div>
