@props(['title', 'sub' => null, 'createUrl' => null, 'createLabel' => 'Tambah'])
<div class="flex flex-wrap items-center justify-between gap-2 mb-4">
  <div>
    <h3 class="section-title">{{ $title }}</h3>
    @if($sub)
      <p class="text-[12.5px] mt-0.5" style="color:var(--ink-500)">{{ $sub }}</p>
    @endif
  </div>
  @if($createUrl)
    <a href="{{ $createUrl }}" class="btn-outline px-3 py-1.5 rounded-lg text-[12px] font-medium flex-none">+ {{ $createLabel }}</a>
  @endif
</div>