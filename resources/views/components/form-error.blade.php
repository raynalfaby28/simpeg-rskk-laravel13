{{-- Compact form-field inline error (§13) --}}
@props([
  'field'   => null,   // nama field (untuk @error)
  'message' => null,   // pesan tetap (tanpa @error)
  'class'   => null,
])
<span class="field-error {{ $class }}" {{ $attributes }}
      @if($field && $errors->has($field))
        role="alert">
        <svg style="width:12.5px;height:12.5px;flex:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9l-8.5 14.8A2 2 0 004.6 21h14.8a2 2 0 001.7-3.3L12.3 3.9a2 2 0 00-3.4 0z"/></svg>
        <span data-field="true">{{ $errors->first($field) }}</span>
      @elseif($message)
        role="status">
        <svg style="width:12.5px;height:12.5px;flex:none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9l-8.5 14.8A2 2 0 004.6 21h14.8a2 2 0 001.7-3.3L12.3 3.9a2 2 0 00-3.4 0z"/></svg>
        <span>{{ $message }}</span>
      @endif
  >
