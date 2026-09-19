@props(['label', 'value' => null])

<div {{ $attributes->class(['metadata-list-item']) }}>
    <dt class="metadata-list-item__label">{{ $label }}</dt>
    <dd class="metadata-list-item__value">{{ filled($value) ? $value : $slot }}</dd>
</div>
