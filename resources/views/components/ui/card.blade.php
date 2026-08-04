@props(['padding' => null, 'width' => null, 'variant' => 'default'])

<div {{ $attributes->class(['card'])->merge([
    'data-padding' => $padding,
    'data-width' => $width,
    'data-variant' => $variant,
]) }}>{{ $slot }}</div>
