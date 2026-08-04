@props(['variant', 'label', 'pulsing' => false])

<span role="img" aria-label="{{ $label }}"
    {{ $attributes->class('statusdot')->merge([
        'data-variant' => $variant,
        'data-pulsing' => $pulsing ? 'true' : null,
    ]) }}></span>
