@props([
    'label',
    'description' => null,
    'href' => null,
    'selected' => false,
    'disabled' => false,
])

<li {{ $attributes->class(['list-item', 'item'])->merge([
    'aria-selected' => $selected ? 'true' : null,
    'aria-disabled' => $disabled ? 'true' : null,
]) }}>
    @isset($start)
        <span class="item__start" aria-hidden="true">{{ $start }}</span>
    @endisset

    @if ($href)
        <a href="{{ $href }}" class="item__action" @if ($disabled) aria-disabled="true" tabindex="-1" @endif>
            <span class="item__label">{{ $label }}</span>
            @if ($description)<span class="item__description">{{ $description }}</span>@endif
        </a>
    @else
        <span class="item__content">
            <span class="item__label">{{ $label }}</span>
            @if ($description)<span class="item__description">{{ $description }}</span>@endif
        </span>
    @endif

    @isset($end)
        <span class="item__end" aria-hidden="true">{{ $end }}</span>
    @endisset
</li>
