@props(['type' => 'error', 'variant' => 'attached'])

<div {{ $attributes->class(['field-status', $type, $variant])->merge([
    'data-type' => $type,
    'data-variant' => $variant,
    'role' => $type === 'error' ? 'alert' : 'status',
    'aria-live' => $type === 'error' ? 'assertive' : 'polite',
]) }}>{{ $slot }}</div>
