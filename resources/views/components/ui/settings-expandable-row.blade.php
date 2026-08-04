@props([
    'field',
    'label',
    'value',
    'action',
    'expanded' => false,
    'includeField' => true,
])

<div class="settings-expandable-row" data-expandable-row @if ($expanded) data-expanded="true" @endif>
    <div data-expandable-row-summary @if ($expanded) hidden @endif>
        <x-ui.stack direction="horizontal" h-align="between" v-align="start" padding-block="4">
            <x-ui.stack direction="vertical" gap="0">
                <x-ui.text type="body" weight="semibold">{{ $label }}</x-ui.text>
                <x-ui.text type="supporting" color="secondary">{{ $value }}</x-ui.text>
            </x-ui.stack>
            <a href="#{{ $field }}-editor" class="link" data-expandable-row-edit aria-controls="{{ $field }}-editor">Editar</a>
        </x-ui.stack>
    </div>

    <form id="{{ $field }}-editor" method="POST" action="{{ $action }}" data-expandable-row-editor @if (! $expanded) hidden @endif>
        @csrf
        @method('PUT')
        @if ($includeField)<input type="hidden" name="campo" value="{{ $field }}">@endif
        <x-ui.stack direction="vertical" gap="4" padding-block="4">
            <x-ui.text type="body" weight="semibold">{{ $label }}</x-ui.text>
            {{ $slot }}
            <x-ui.stack direction="horizontal" gap="2">
                <x-ui.button type="submit" variant="primary">Guardar</x-ui.button>
                <x-ui.button type="button" variant="ghost" data-expandable-row-cancel>Cancelar</x-ui.button>
            </x-ui.stack>
        </x-ui.stack>
    </form>
    <x-ui.divider />
</div>
