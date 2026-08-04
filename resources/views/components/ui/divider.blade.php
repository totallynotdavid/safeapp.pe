@props(['label' => null, 'variant' => 'subtle'])

<div role="separator" aria-orientation="horizontal"
    {{ $attributes->class(['divider'])->merge(['data-variant' => $variant]) }}>
    <div class="divider__line"></div>
    @if ($label)
        <div class="divider__label">{{ $label }}</div>
        <div class="divider__line"></div>
    @endif
</div>
