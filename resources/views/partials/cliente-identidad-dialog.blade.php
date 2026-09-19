<x-ui.dialog id="cliente-identidad" title="Registrar identidad" width="460" open-on-load>
    <form id="cliente-identidad-form" method="POST" action="{{ route($rutaIdentidad) }}">
        @csrf
        <x-ui.stack direction="vertical" gap="3">
            <x-ui.text-input name="ruc" label="RUC" :value="old('ruc', $ruc)" inputmode="numeric" maxlength="11"
                :error="$errors->first('ruc')" required />
            <x-ui.text-input name="razon_social" label="Razón social" :value="old('razon_social')"
                maxlength="255" :error="$errors->first('razon_social')" required />
        </x-ui.stack>
    </form>
    <x-slot:footer>
        <x-ui.stack direction="horizontal" gap="2" h-align="end">
            <x-ui.button type="button" variant="secondary" data-dialog-close>Cancelar</x-ui.button>
            <x-ui.button type="submit" form="cliente-identidad-form">Continuar</x-ui.button>
        </x-ui.stack>
    </x-slot:footer>
</x-ui.dialog>
