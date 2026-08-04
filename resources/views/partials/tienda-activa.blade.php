{{-- Only shown when there is a real choice. Selector source:
     packages/core/src/Selector/{Selector.tsx,Selector.doc.mjs,Selector.test.tsx}
     @ 9da0792516e4a551494e2d212f0a03909a8ffd71. --}}
@if ($tiendasAsignadas->count() > 1)
    @php
        $controlId = ($idPrefix ?? '').'id_tienda';
        $storeOptions = $tiendasAsignadas->map(fn ($asignacion) => [
            'value' => (string) $asignacion->id_tienda,
            'label' => $asignacion->tienda?->nombre ?? 'Tienda '.$asignacion->id_tienda,
        ])->all();
        $activeStore = (string) ($tiendasAsignadas->firstWhere('estado', 0)?->id_tienda ?? '');
    @endphp

    <form method="POST" action="{{ route('tienda-activa.update') }}" class="store-switcher">
        @csrf
        @method('PUT')
        <x-ui.selector
            :id="$controlId"
            name="id_tienda"
            label="Tienda activa"
            :options="$storeOptions"
            :value="$activeStore"
            :required="true"
            :auto-submit="true"
        />
        <noscript>
            <x-ui.button type="submit" variant="secondary" size="sm">Cambiar</x-ui.button>
        </noscript>
    </form>
@endif
