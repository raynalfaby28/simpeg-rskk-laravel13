{{-- Compact enterprise empty state (proportional, NOT decorative) --}}
@props([
  'title' => 'Belum Ada Data',
  'desc'  => null,
  'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6a2 2 0 011.4.6l1.4 1.4a2 2 0 001.4.6H19a2 2 0 012 2v11a2 2 0 01-2 2z"/>',
  'action' => null,
  'class'  => null,
])
<div class="empty-state {{ $class }}" {{ $attributes }}>
  <div class="es-ic">
    <svg style="width:24px;height:24px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $icon !!}</svg>
  </div>
  <p class="font-semibold text-[13.5px]" style="color:var(--ink-700)">{{ $title }}</p>
  @if($desc)<p class="text-[12.5px] mt-1 max-w-sm mx-auto" style="color:var(--ink-500)">{{ $desc }}</p>@endif
  @if($action)
    <div class="mt-4 inline-flex justify-center">{!! $action !!}</div>
  @elseif($slot->isNotEmpty())
    <div class="mt-4 inline-flex justify-center">{{ $slot }}</div>
  @endif
</div>
