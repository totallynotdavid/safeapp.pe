@extends('layouts.app')

@section('title', 'Seguimiento móvil #'.$cargo->id_cargo)
@section('page', true)

@section('content')
    @php
        $regionOptions = $regiones->map(fn ($region) => [
            'value' => $region->id_tienda,
            'label' => $region->nombre,
        ])->all();
        $carteraOptions = collect([['value' => '', 'label' => 'Sin asignar']])
            ->concat($carteras->map(fn ($cartera) => [
                'value' => $cartera->id_cartera,
                'label' => $cartera->propietario,
            ]))->all();
    @endphp

    <x-ui.detail-page title="Seguimiento móvil #{{ $cargo->id_cargo }}"
        :subtitle="$cargo->cliente?->razon_social"
        :back-href="route('notas-cargo.movil.back.index')" back-label="Volver a notas en seguimiento">
        <x-slot:metadata>
            <x-ui.text type="supporting" color="secondary">・ RUC {{ $cargo->cliente?->ruc }}</x-ui.text>
            <x-ui.text type="supporting" color="secondary">・</x-ui.text>
            <x-ui.badge>{{ \App\Support\EtiquetasDeDominio::texto($cargo->estado, 'estado') }}</x-ui.badge>
            <x-ui.text type="supporting" color="secondary">・ {{ $cargo->fecha_ingreso?->format('d/m/Y') }}</x-ui.text>
        </x-slot:metadata>

        @include('notas-cargo.movil.partials.detalle', ['mostrarSeguimientoBack' => false])

        <x-ui.card padding="6">
            <form method="POST" action="{{ route('notas-cargo.movil.back.update', $cargo) }}">
                @csrf
                @method('PUT')

                <x-ui.form-layout>
                    <x-ui.stack direction="vertical" gap="0.5">
                        <x-ui.heading level="2">Actualizar seguimiento</x-ui.heading>
                        <x-ui.text type="supporting" color="secondary">Registre el avance operativo y las referencias producidas por back office.</x-ui.text>
                    </x-ui.stack>

                    <x-ui.stack direction="vertical" gap="3">
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="3">Estado operativo</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Situación actual, fecha del cambio y fase comercial.</x-ui.text>
                        </x-ui.stack>
                        <x-ui.grid min-width="180" repeat="fit" gap="3">
                            <x-ui.selector name="estado" label="Estado" :options="\App\Support\EtiquetasDeDominio::opciones($estados, 'estado')"
                                :value="old('estado', $cargo->estado)" :error="$errors->first('estado')" required />
                            <x-ui.date-input name="fecha_actualizacion" label="Fecha del estado"
                                :value="old('fecha_actualizacion', $cargo->fecha_actualizacion?->format('Y-m-d'))"
                                :error="$errors->first('fecha_actualizacion')" required />
                            <x-ui.selector name="etapa" label="Fase" :options="\App\Support\EtiquetasDeDominio::opciones($etapas, 'etapa')"
                                :value="old('etapa', $cargo->etapa)" :error="$errors->first('etapa')" required />
                        </x-ui.grid>
                    </x-ui.stack>

                    <x-ui.divider />

                    <x-ui.stack direction="vertical" gap="3">
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="3">Asignación técnica</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Corrija la región si corresponde y complete la atribución operativa.</x-ui.text>
                        </x-ui.stack>
                        <x-ui.grid min-width="180" repeat="fit" gap="3">
                            <x-ui.selector name="zonal" label="Región" :options="$regionOptions"
                                :value="old('zonal', $cargo->zonal)" :error="$errors->first('zonal')" required />
                            <x-ui.selector name="id_cartera" label="Ejecutivo TDP" :options="$carteraOptions"
                                :value="old('id_cartera', $cargo->id_cartera ?: '')" :error="$errors->first('id_cartera')" optional />
                            <x-ui.text-input name="nodo" label="Nodo" maxlength="100"
                                :value="old('nodo', $cargo->nodo)" :error="$errors->first('nodo')" required />
                        </x-ui.grid>
                    </x-ui.stack>

                    <x-ui.divider />

                    <x-ui.stack direction="vertical" gap="3">
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="3">Referencias del caso</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Identificadores complementarios generados durante la atención.</x-ui.text>
                        </x-ui.stack>
                        <x-ui.grid min-width="180" repeat="fit" gap="3">
                            <x-ui.text-input name="oportunidad" label="Oportunidad" maxlength="100"
                                :value="old('oportunidad', $cargo->oportunidad)" :error="$errors->first('oportunidad')" optional />
                            <x-ui.text-input name="casofwa" label="Caso FWA" maxlength="100"
                                :value="old('casofwa', $cargo->casofwa)" :error="$errors->first('casofwa')" optional />
                            <x-ui.text-input name="ciclo" label="Ciclo" maxlength="50"
                                :value="old('ciclo', $cargo->ciclo)" :error="$errors->first('ciclo')" optional />
                            <x-ui.text-input name="cuenta_financiera" label="Cuenta financiera" maxlength="100"
                                :value="old('cuenta_financiera', $cargo->cuenta_financiera)"
                                :error="$errors->first('cuenta_financiera')" optional />
                        </x-ui.grid>
                    </x-ui.stack>

                    <x-ui.text-area name="comentario_back" label="Comentario de back office"
                        :value="old('comentario_back', $cargo->comentario_back)" :error="$errors->first('comentario_back')"
                        rows="4" required />

                    <x-ui.stack direction="horizontal" gap="2" h-align="end" wrap="wrap">
                        <x-ui.button :href="route('notas-cargo.movil.back.index')" variant="secondary">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="primary">Guardar seguimiento</x-ui.button>
                    </x-ui.stack>
                </x-ui.form-layout>
            </form>
        </x-ui.card>
    </x-ui.detail-page>
@endsection
