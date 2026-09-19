@extends('layouts.app')

@section('title', 'Nota de cargo móvil #'.$cargo->id_cargo)
@section('page', true)

@section('content')
    @php
        $nivelOptions = collect($niveles)->map(fn ($nivel) => [
            'value' => $nivel,
            'label' => $nivel.' %',
        ])->all();
    @endphp

    <x-ui.detail-page title="Nota de cargo móvil #{{ $cargo->id_cargo }}"
        :subtitle="$cargo->cliente?->razon_social"
        :back-href="route('notas-cargo.movil.index')" back-label="Volver a mis notas de cargo">
        <x-slot:metadata>
            <x-ui.text type="supporting" color="secondary">・ RUC {{ $cargo->cliente?->ruc }}</x-ui.text>
            <x-ui.text type="supporting" color="secondary">・</x-ui.text>
            <x-ui.badge>{{ \App\Support\EtiquetasDeDominio::texto($cargo->estado, 'estado') }}</x-ui.badge>
            <x-ui.text type="supporting" color="secondary">・ {{ $cargo->fecha_ingreso?->format('d/m/Y') }}</x-ui.text>
        </x-slot:metadata>

        @include('notas-cargo.movil.partials.detalle', [
            'mostrarSeguimientoEjecutivo' => ! $cargo->esEditable(),
        ])

        @if ($cargo->esEditable())
            <x-ui.card padding="6">
                <form method="POST" action="{{ route('notas-cargo.movil.update', $cargo) }}">
                    @csrf
                    @method('PUT')

                    <x-ui.form-layout>
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="2">Actualizar negociación</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Ajuste el alcance, los teléfonos y el avance comercial mientras la nota siga pendiente de validación.</x-ui.text>
                        </x-ui.stack>

                        <x-ui.stack direction="vertical" gap="3">
                            <x-ui.stack direction="vertical" gap="0.5">
                                <x-ui.heading level="3">Alcance económico</x-ui.heading>
                                <x-ui.text type="supporting" color="secondary">Valores que revisará el equipo validador.</x-ui.text>
                            </x-ui.stack>
                            <x-ui.grid min-width="180" repeat="fit" gap="3">
                                <x-ui.text-input name="q_lineas" label="Líneas" type="number" min="1"
                                    :value="old('q_lineas', $cargo->q_lineas)" :error="$errors->first('q_lineas')" required />
                                <x-ui.text-input name="cargo_fijo" label="Cargo fijo" type="number" min="0" step="0.01"
                                    :value="old('cargo_fijo', $cargo->cargo_fijo)" :error="$errors->first('cargo_fijo')" required />
                            </x-ui.grid>
                        </x-ui.stack>

                        <x-ui.divider />

                        <x-ui.stack direction="vertical" gap="3">
                            <x-ui.stack direction="vertical" gap="0.5">
                                <x-ui.heading level="3">Contacto</x-ui.heading>
                                <x-ui.text type="supporting" color="secondary">Teléfonos para coordinar con {{ $cargo->contacto }}.</x-ui.text>
                            </x-ui.stack>
                            <x-ui.grid min-width="180" repeat="fit" gap="3">
                                <x-ui.text-input name="telefono1" label="Teléfono principal" maxlength="50"
                                    :value="old('telefono1', $cargo->telefono1)" :error="$errors->first('telefono1')" required />
                                <x-ui.text-input name="telefono2" label="Teléfono adicional" maxlength="50"
                                    :value="old('telefono2', $cargo->telefono2)" :error="$errors->first('telefono2')" optional />
                            </x-ui.grid>
                        </x-ui.stack>

                        <x-ui.divider />

                        <x-ui.stack direction="vertical" gap="3">
                            <x-ui.stack direction="vertical" gap="0.5">
                                <x-ui.heading level="3">Avance comercial</x-ui.heading>
                                <x-ui.text type="supporting" color="secondary">Probabilidad, fecha objetivo y fase actual de la oportunidad.</x-ui.text>
                            </x-ui.stack>
                            <x-ui.grid min-width="180" repeat="fit" gap="3">
                                <x-ui.selector name="nivel_negociacion" label="Probabilidad de cierre" :options="$nivelOptions"
                                    :value="old('nivel_negociacion', $cargo->nivel_negociacion)"
                                    :error="$errors->first('nivel_negociacion')" required />
                                <x-ui.date-input name="fecha_estimada" label="Fecha estimada de cierre"
                                    :value="old('fecha_estimada', $cargo->fecha_estimada?->format('Y-m-d'))"
                                    :error="$errors->first('fecha_estimada')" required />
                                <x-ui.selector name="etapa" label="Fase" :options="\App\Support\EtiquetasDeDominio::opciones($etapas, 'etapa')"
                                    :value="old('etapa', $cargo->etapa)" :error="$errors->first('etapa')" required />
                            </x-ui.grid>

                            <x-ui.text-input name="oportunidad" label="Oportunidad" maxlength="100"
                                :value="old('oportunidad', $cargo->oportunidad)" :error="$errors->first('oportunidad')" optional />
                            <x-ui.text-area name="comentario_ejecutivo" label="Comentario del ejecutivo"
                                :value="old('comentario_ejecutivo', $cargo->comentario_ejecutivo)"
                                :error="$errors->first('comentario_ejecutivo')" rows="4" optional />
                        </x-ui.stack>

                        <x-ui.stack direction="horizontal" gap="2" h-align="end" wrap="wrap">
                            <x-ui.button :href="route('notas-cargo.movil.index')" variant="secondary">Cancelar</x-ui.button>
                            <x-ui.button type="submit" variant="primary">Guardar cambios</x-ui.button>
                        </x-ui.stack>
                    </x-ui.form-layout>
                </form>
            </x-ui.card>
        @else
            <x-ui.banner status="warning" title="Negociación cerrada"
                description="Esta nota ya fue validada. La información registrada por el ejecutivo se conserva en modo de solo lectura." />
        @endif
    </x-ui.detail-page>
@endsection
