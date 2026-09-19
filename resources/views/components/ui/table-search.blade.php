@props([
    'action',
    'name' => 'buscar',
    'value' => '',
    'placeholder' => 'Buscar…',
    'resultCount' => null,
    'label' => 'Buscar',
    'preserve' => [],
])

<form action="{{ $action }}" method="GET" role="search" class="table-search">
    @foreach ($preserve as $key => $preservedValue)
        <input type="hidden" name="{{ $key }}" value="{{ $preservedValue }}">
    @endforeach

    <label class="visually-hidden" for="{{ $name }}">{{ $label }}</label>
    <div class="text-input" data-size="md">
        <x-ui.icon name="magnifying-glass" size="sm" color="secondary" />
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="search"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            class="text-input__control"
        >
        @if ($resultCount !== null)
            <span class="table-search__result-count">{{ $resultCount }} {{ $resultCount === 1 ? 'resultado' : 'resultados' }}</span>
        @endif
    </div>
</form>
