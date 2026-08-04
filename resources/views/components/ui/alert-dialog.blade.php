@props([
    'id',
    'title',
    'description',
    'actionLabel',
    'cancelLabel' => 'Cancelar',
    'actionVariant' => 'destructive',
    'form' => null,
])

@php
    $titleId = $id.'-title';
    $descriptionId = $id.'-description';
@endphp

<dialog id="{{ $id }}"
        class="dialog alert-dialog"
        role="alertdialog"
        aria-labelledby="{{ $titleId }}"
        aria-describedby="{{ $descriptionId }}"
        data-alert-dialog>
    <div class="dialog__inner">
        <div class="layout" data-height="auto">
            <div class="layout-content" data-padding="4">
                <x-ui.heading level="2" :id="$titleId">{{ $title }}</x-ui.heading>
                <x-ui.text type="body" color="secondary" :id="$descriptionId">{{ $description }}</x-ui.text>
            </div>
            <footer class="layout-footer">
                <div class="layout-footer__inner">
                    <div class="stack" data-direction="horizontal" data-gap="2" data-h-align="end" data-v-align="center">
                        <x-ui.button type="button" variant="ghost" data-alert-dialog-cancel>{{ $cancelLabel }}</x-ui.button>
                        <x-ui.button type="submit" :variant="$actionVariant" :form="$form">{{ $actionLabel }}</x-ui.button>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</dialog>
