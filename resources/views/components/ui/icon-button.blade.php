@props([
    'label',
    'type' => 'button',
    'variant' => 'secondary',
    'size' => 'md',
    'disabled' => false,
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="{{ $type }}" @disabled($disabled) @endif
    aria-label="{{ $label }}"
    {{ $attributes->class(['button', $variant, $size])->merge([
        'data-variant' => $variant,
        'data-size' => $size,
        'data-icon-only' => 'true',
        'aria-disabled' => $href && $disabled ? 'true' : null,
    ]) }}
>
    <span class="button__content">
        <span class="button__icon" aria-hidden="true">{{ $slot }}</span>
    </span>
    <span class="visually-hidden" role="status" aria-live="polite"></span>
</{{ $tag }}>
