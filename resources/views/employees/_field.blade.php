@php
  $errorsBag = $errors ?? null;
  $fieldValue = old($name, $employee->{$name} ?? null);
  if ($fieldValue instanceof \DateTimeInterface) {
      $fieldValue = $fieldValue->format('Y-m-d');
  }
  $type = $type ?? 'text';
  $hasError = $errorsBag && $errorsBag->has($name);
  $errMsg = $hasError ? $errorsBag->first($name) : null;
@endphp
<label for="{{ $name }}" class="flabel">
  {{ $label }}
  @if(!empty($required)) <span class="req">*</span> @endif
</label>
<input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $fieldValue }}" @if(!empty($required)) required @endif
  placeholder="{{ $placeholder ?? '' }}"
  class="input"
  style="border:1px solid {{ $hasError ? 'var(--red-400)' : 'var(--line)' }}; {{ $hasError ? 'box-shadow:0 0 0 2px rgba(244,63,94,.10);' : '' }} background:{{ $hasError ? 'var(--red-50)' : 'transparent' }}">
@if($hasError)
  <p class="text-[11px] mt-1 font-medium" style="color:var(--red-600)">⚠ {{ $errMsg }}</p>
@endif
@if(!empty($help))
  <p class="text-[11px] mt-1" style="color:var(--ink-300)">{{ $help }}</p>
@endif