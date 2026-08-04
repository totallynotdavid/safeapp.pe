@props(['title', 'description' => null, 'headingLevel' => 3, 'compact' => false])

<div role="status" {{ $attributes->class('empty-state')->merge(['data-variant' => $compact ? 'compact' : null]) }}>
    @isset($icon)<div class="empty-state__icon" aria-hidden="true">{{ $icon }}</div>@endisset
    <div class="empty-state__text-group">
        <h{{ $headingLevel }} class="empty-state__title">{{ $title }}</h{{ $headingLevel }}>
        @if ($description)<div class="empty-state__description">{{ $description }}</div>@endif
    </div>
    @isset($actions)<div class="empty-state__actions">{{ $actions }}</div>@endisset
</div>
