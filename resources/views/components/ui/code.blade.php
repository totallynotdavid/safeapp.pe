@props(['color' => 'primary'])

<code {{ $attributes->class(['code'])->merge(['data-color' => $color]) }}>{{ $slot }}</code>
