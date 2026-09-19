{{--
    One GET form owns both the visible query and the export, so every queue
    exports exactly the period and state shown on screen.

    Expects: $ruta, $rutaExportar, $filtro, $estados, $formId.
--}}
@php
    $meses = [
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
        '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
        '09' => 'Setiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
    ];
    $yearOptions = collect($filtro->anos())
        ->map(fn ($option) => ['value' => (string) $option, 'label' => (string) $option])
        ->all();
    $monthOptions = collect($meses)
        ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
        ->values()
        ->all();
    $statusOptions = collect([['value' => '', 'label' => 'Todos los estados']])
        ->concat(\App\Support\EtiquetasDeDominio::opciones($estados, 'estado'))
        ->all();
@endphp

<x-ui.stack id="{{ $formId }}" as="form" method="GET" action="{{ route($ruta) }}"
    direction="horizontal" v-align="end" gap="3" wrap="wrap">
    <x-ui.selector id="{{ $formId }}-ano" name="ano" label="Año"
        :options="$yearOptions" :value="(string) $filtro->ano" />
    <x-ui.selector id="{{ $formId }}-mes" name="mes" label="Mes"
        :options="$monthOptions" :value="str_pad((string) $filtro->mes, 2, '0', STR_PAD_LEFT)" />
    <x-ui.selector id="{{ $formId }}-estado" name="estado" label="Estado"
        :options="$statusOptions" :value="$filtro->estado ?? ''" />
    <x-ui.button type="submit" variant="primary">Aplicar filtros</x-ui.button>
    <x-ui.button type="submit" variant="secondary" formaction="{{ route($rutaExportar) }}">
        <x-slot:icon><x-ui.icon name="arrow-down-tray" /></x-slot:icon>
        Exportar
    </x-ui.button>
</x-ui.stack>
