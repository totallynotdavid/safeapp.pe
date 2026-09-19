@props(['variant' => 'neutral'])

<span {{ $attributes->class('badge')->merge(['data-variant' => $variant]) }}>{{ $slot }}</span>
