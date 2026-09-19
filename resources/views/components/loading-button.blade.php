{{-- Button loading state (§14): swap label+"Menyimpan..." + spinner, tetap compact --}}
@props([
  'label'    => null,          // fallback slot
  'loading'  => 'Menyimpan...',
  'type'     => 'submit',
  'class'    => 'btn btn-primary',
  'spinner'  => true,
  'delay'    => 80,            // ms sebelum swap agar klik kelihatan responsif
])
<button type="{{ $type }}" class="{{ $class }}" data-loading-btn
        @if($type === 'submit') @endif
        data-loading-label="{{ $label }}" data-loading-text="{{ $loading }}"
        {{ $attributes }}>
  @if($spinner)
    <svg data-lb-ic style="width:15px;height:15px;flex:none" class="lb-hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" d="M12 3a9 9 0 109 9" stroke-linejoin="round"/>
    </svg>
  @endif
  <span data-lb-tx>{{ $label ?? $slot }}</span>
</button>
