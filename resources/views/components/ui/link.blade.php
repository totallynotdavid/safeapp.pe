@props(['href', 'color' => 'accent', 'standalone' => false])

<a href="{{ $href }}" {{ $attributes->class(['link'])->merge([
    'data-color' => $color,
    'data-standalone' => $standalone ? 'true' : null,
]) }}>{{ $slot }}</a>
