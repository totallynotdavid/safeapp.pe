@props([
    'type' => 'button',
    'variant' => 'secondary',
    'size' => 'md',
    'width' => null,
    'disabled' => false,
    'href' => null,
    'iconOnly' => false,
])

@php
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="{{ $type }}" @disabled($disabled) @endif
    {{ $attributes->class(['button', $variant, $size])->merge([
        'data-variant' => $variant,
        'data-size' => $size,
        'data-width' => $width,
        'data-icon-only' => $iconOnly ? 'true' : null,
        'aria-disabled' => $href && $disabled ? 'true' : null,
    ]) }}>
    <span class="button__content">
        @isset($icon)<span class="button__icon" aria-hidden="true">{{ $icon }}</span>@endisset
        @if ($iconOnly)
            <span class="visually-hidden">{{ $slot }}</span>
        @else
            <span class="button__label">{{ $slot }}</span>
        @endif
    </span>
    <span class="visually-hidden" role="status" aria-live="polite"></span>
</{{ $tag }}>
