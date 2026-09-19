@props(['label' => 'Tabla de datos', 'density' => 'balanced', 'dividers' => 'rows', 'hover' => true, 'truncate' => false])

<div tabindex="0" role="group" aria-label="{{ $label }}" class="table-scroll-wrapper">
    <table {{ $attributes->class('table')->merge([
        'data-density' => $density,
        'data-dividers' => $dividers,
        'data-hover' => $hover ? 'true' : 'false',
        'data-text-overflow' => $truncate ? 'truncate' : 'wrap',
    ]) }}>{{ $slot }}</table>
</div>
