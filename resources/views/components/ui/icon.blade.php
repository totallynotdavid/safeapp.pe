@php
    $iconAttributes = [
        'data-theme' => 'icon',
        'data-size' => $size,
        'data-color' => $color,
        'aria-hidden' => $label === null ? 'true' : 'false',
    ];

    if ($label !== null) {
        $iconAttributes['role'] = 'img';
        $iconAttributes['aria-label'] = $label;
    }
@endphp

<x-dynamic-component
    :component="$component"
    {{ $attributes->class('icon')->merge($iconAttributes) }}
/>
