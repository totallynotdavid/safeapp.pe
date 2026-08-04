@props(['type' => 'body', 'color' => null, 'size' => null, 'weight' => null])

<span {{ $attributes->class(['text'])->merge([
    'data-type' => $type,
    'data-color' => $color,
    'data-size' => $size,
    'data-weight' => $weight,
]) }}>{{ $slot }}</span>
