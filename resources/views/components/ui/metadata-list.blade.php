@props(['columns' => 'single', 'labelPosition' => null, 'title' => null])

@php
    $isMulti = $columns === 'multi' || (is_numeric($columns) && (int) $columns > 1);
    $position = $labelPosition ?? ($isMulti ? 'top' : 'start');
@endphp

<div {{ $attributes->class(['metadata-list'])->merge([
    'data-columns' => (string) $columns,
    'data-label-position' => $position,
]) }}>
    @if ($title)
        <div class="metadata-list__title">{{ $title }}</div>
    @endif
    <dl class="metadata-list__content">{{ $slot }}</dl>
</div>
