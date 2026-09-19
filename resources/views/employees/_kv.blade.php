<div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2">
  @foreach ($items as $label => $value)
    <div class="field-row">
      <span style="color:var(--ink-500)">{{ $label }}</span>
      <span class="text-right font-medium" style="color:var(--ink-700)">{!! $value ? e($value) : '' !!}</span>
    </div>
  @endforeach
</div>