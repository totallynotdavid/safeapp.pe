@props(['label', 'value'])

<x-ui.stack direction="vertical" gap="0">
    <x-ui.stack direction="horizontal" h-align="between" v-align="start" padding-block="4">
        <x-ui.stack direction="vertical" gap="0">
            <x-ui.text type="body" weight="semibold">{{ $label }}</x-ui.text>
            <x-ui.text type="supporting" color="secondary">{{ $value }}</x-ui.text>
        </x-ui.stack>
        @isset($action)<div>{{ $action }}</div>@endisset
    </x-ui.stack>
    <x-ui.divider />
</x-ui.stack>
