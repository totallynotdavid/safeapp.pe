{{--
    A GET form pointed back at the screen that included it, so the resolved
    state is a URL the agent can reload or share.

    Expects: $ruta (route name to submit to), $ruc (current value or null).
--}}
<x-ui.card padding="4">
    <form method="GET" action="{{ route($ruta) }}">
        <x-ui.stack direction="vertical" gap="3">
            <x-ui.stack direction="vertical" gap="0.5">
                <x-ui.heading level="3">Buscar por RUC</x-ui.heading>
                <x-ui.text type="supporting" color="secondary">Ingrese los 11 dígitos para consultar el estado del cliente.</x-ui.text>
            </x-ui.stack>
            <x-ui.stack direction="horizontal" gap="2" v-align="end">
                <div data-theme="stack-item" data-size="fill">
                    <x-ui.text-input id="ruc" name="ruc" label="RUC del cliente" :value="$ruc ?? ''"
                        inputmode="numeric" maxlength="11" autocomplete="off" required autofocus />
                </div>
                <x-ui.button type="submit" variant="primary">Buscar</x-ui.button>
            </x-ui.stack>
        </x-ui.stack>
    </form>
</x-ui.card>
