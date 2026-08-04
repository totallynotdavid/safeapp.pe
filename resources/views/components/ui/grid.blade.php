@props([
    'columns' => 1,
    'minWidth' => null,
    'repeat' => 'fill',
    'gap' => null,
    'align' => null,
])

@php
    $repeatMode = $repeat === 'fit' ? 'auto-fit' : 'auto-fill';
    $template = $minWidth !== null
        ? "repeat({$repeatMode}, minmax({$minWidth}px, 1fr))"
        : 'repeat('.max(1, (int) $columns).', 1fr)';
@endphp

<div {{ $attributes->class(['grid'])->merge([
    'data-gap' => $gap,
    'data-align' => $align,
    'style' => "--grid-template-columns: {$template}",
]) }}>{{ $slot }}</div>
