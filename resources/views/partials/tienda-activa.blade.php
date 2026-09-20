@if (count($tiendasDisponibles) > 1)
    @php($controlId = ($idPrefix ?? '').'id_tienda')

    <form method="POST" action="{{ route('tienda-activa.update') }}" class="store-switcher">
        @csrf
        @method('PUT')
        <x-ui.selector
            :id="$controlId"
            name="id_tienda"
            label="Tienda activa"
            :options="$tiendasDisponibles"
            :value="$tiendaActiva"
            :required="true"
            :auto-submit="true"
        />
        <noscript>
            <x-ui.button type="submit" variant="secondary" size="sm">Cambiar</x-ui.button>
        </noscript>
    </form>
@endif
