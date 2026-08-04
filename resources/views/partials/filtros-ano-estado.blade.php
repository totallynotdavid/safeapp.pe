{{--
    Shared compact queue filter. It preserves each queue's GET contract while
    using the same Stack spacing, alignment, labels and action hierarchy.
--}}
@php
    $yearOptions = collect($anos)->map(fn ($option) => ['value' => (string) $option, 'label' => (string) $option])->all();
    $statusOptions = collect([['value' => '', 'label' => 'Todos los estados']])
        ->concat(\App\Support\EtiquetasDeDominio::opciones($estados, 'estado'))
        ->all();
@endphp

<x-ui.stack id="{{ $formId }}" as="form" method="GET" action="{{ route($ruta) }}"
    direction="horizontal" gap="3" v-align="end" wrap="wrap">
    <x-ui.selector id="{{ $formId }}-ano" name="ano" label="Año" :options="$yearOptions" :value="(string) $ano" />
    <x-ui.selector id="{{ $formId }}-estado" name="estado" label="Estado" :options="$statusOptions" :value="$estado ?? ''" />
    <x-ui.button type="submit" variant="primary">Aplicar filtros</x-ui.button>
</x-ui.stack>
