@props(['status' => 'info', 'title', 'description' => null])

@php($role = in_array($status, ['warning', 'error'], true) ? 'alert' : 'status')

@php($hasContent = $slot->isNotEmpty())

<div {{ $attributes->class(['banner'])->merge(['role' => $role, 'data-status' => $status]) }}>
    <div class="banner__header" @if ($hasContent) data-has-content="true" @endif>
        <div class="banner__icon" aria-hidden="true">
            @switch($status)
                @case('warning') <x-ui.icon name="exclamation-triangle" /> @break
                @case('error') <x-ui.icon name="exclamation-circle" /> @break
                @case('success') <x-ui.icon name="check-circle" /> @break
                @default <x-ui.icon name="information-circle" />
            @endswitch
        </div>
        <div class="banner__content">
            <div class="banner__title">{{ $title }}</div>
            @if ($description)
                <div class="banner__description">{{ $description }}</div>
            @endif
        </div>
    </div>
    @if ($hasContent)
        <div class="banner__content-area">{{ $slot }}</div>
    @endif
</div>
