@props([
    'id',
    'title',
    'description' => null,
    'width' => 400,
    'openOnLoad' => false,
])

@php
    $titleId = $id.'-title';
    $descriptionId = $description ? $id.'-description' : null;
@endphp

<dialog id="{{ $id }}"
        class="dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $titleId }}"
        @if ($descriptionId) aria-describedby="{{ $descriptionId }}" @endif
        data-dialog
        @if ($openOnLoad) data-dialog-open-on-load @endif
        style="width: {{ is_numeric($width) ? $width.'px' : $width }}">
    <div class="dialog__inner">
        <div class="layout" data-height="auto">
            <header class="layout-header" data-divider="true">
                <div class="layout-header__inner">
                    <div class="dialog__header">
                        <div class="dialog__title-wrapper">
                            <x-ui.heading level="2" :id="$titleId" tabindex="-1" data-dialog-title>{{ $title }}</x-ui.heading>
                        </div>
                        <x-ui.icon-button label="Cerrar" variant="ghost" size="md" data-dialog-close>
                            <x-ui.icon name="x-mark" size="sm" />
                        </x-ui.icon-button>
                    </div>
                </div>
            </header>

            <div class="layout-content dialog__content" data-padding="4">
                <div class="stack" data-direction="vertical" data-gap="4">
                    @if ($description)<x-ui.text type="body" color="secondary" :id="$descriptionId">{{ $description }}</x-ui.text>@endif
                    {{ $slot }}
                </div>
            </div>

            @isset($footer)
                <footer class="layout-footer" data-divider="true">
                    <div class="layout-footer__inner">{{ $footer }}</div>
                </footer>
            @endisset
        </div>
    </div>
</dialog>
