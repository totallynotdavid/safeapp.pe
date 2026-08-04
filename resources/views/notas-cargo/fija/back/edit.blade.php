@extends('layouts.app')

@section('title', 'Seguimiento fija #'.$cargo->id_cargo)
@section('page', true)

@section('content')
    @php
        $regionOptions = $regiones->map(fn ($region) => [
            'value' => $region->id_tienda,
            'label' => $region->nombre,
        ])->all();
    @endphp

    <x-ui.detail-page title="Seguimiento fija #{{ $cargo->id_cargo }}"
        :subtitle="$cargo->cliente?->razon_social"
        :back-href="route('notas-cargo.fija.back.index')" back-label="Volver a notas en seguimiento">
        <x-slot:metadata>
            <x-ui.text type="supporting" color="secondary">・ RUC {{ $cargo->cliente?->ruc }}</x-ui.text>
            <x-ui.text type="supporting" color="secondary">・</x-ui.text>
            <x-ui.badge>{{ \App\Support\EtiquetasDeDominio::texto($cargo->estado, 'estado') }}</x-ui.badge>
            <x-ui.text type="supporting" color="secondary">・ {{ $cargo->fecha_ingreso?->format('d/m/Y') }}</x-ui.text>
        </x-slot:metadata>

        @include('notas-cargo.fija.partials.detalle', ['mostrarSeguimientoBack' => false])

        <x-ui.card padding="6">
            <form method="POST" action="{{ route('notas-cargo.fija.back.update', $cargo) }}">
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
                            <x-ui.text type="supporting" color="secondary">Situación actual y fecha de cierre cuando la instalación queda completada.</x-ui.text>
                        </x-ui.stack>
                        <x-ui.grid min-width="180" repeat="fit" gap="3">
                            <x-ui.selector name="estado" label="Estado" :options="\App\Support\EtiquetasDeDominio::opciones($estados, 'estado')"
                                :value="old('estado', $cargo->estado)" :error="$errors->first('estado')" required />
                            <x-ui.date-input name="fecha_liquidacion" label="Fecha de liquidación"
                                description="Obligatoria cuando el estado es COMPLETADO."
                                :value="old('fecha_liquidacion', $cargo->fecha_liquidacion?->format('Y-m-d'))"
                                :error="$errors->first('fecha_liquidacion')" has-clear />
                        </x-ui.grid>
                    </x-ui.stack>

                    <x-ui.divider />

                    <x-ui.stack direction="vertical" gap="3">
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="3">Asignación técnica</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Corrija la región si corresponde y registre la situación del nodo.</x-ui.text>
                        </x-ui.stack>
                        <x-ui.grid min-width="180" repeat="fit" gap="3">
                            <x-ui.selector name="zonal" label="Región" :options="$regionOptions"
                                :value="old('zonal', $cargo->zonal)" :error="$errors->first('zonal')" required />
                            <x-ui.selector name="nodo" label="Nodo" :options="\App\Support\EtiquetasDeDominio::opciones($nodos, 'nodo')"
                                :value="old('nodo', $cargo->nodo)" :error="$errors->first('nodo')" required />
                        </x-ui.grid>
                    </x-ui.stack>

                    <x-ui.divider />

                    <x-ui.stack direction="vertical" gap="3">
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="3">Referencias del caso</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Identificadores generados durante la instalación, facturación y coordinación técnica.</x-ui.text>
                        </x-ui.stack>
                        <x-ui.grid min-width="180" repeat="fit" gap="3">
                            <x-ui.text-input name="peticion" label="Petición" maxlength="100"
                                :value="old('peticion', $cargo->peticion)" :error="$errors->first('peticion')" optional />
                            <x-ui.text-input name="contrata" label="Contrata" maxlength="100"
                                :value="old('contrata', $cargo->contrata)" :error="$errors->first('contrata')" optional />
                            <x-ui.text-input name="nasignado" label="Número asignado" maxlength="100"
                                :value="old('nasignado', $cargo->nasignado)" :error="$errors->first('nasignado')" optional />
                            <x-ui.text-input name="caso_sf" label="Caso SF" maxlength="100"
                                :value="old('caso_sf', $cargo->caso_sf)" :error="$errors->first('caso_sf')" optional />
                            <x-ui.text-input name="ncfinanciera" label="NC financiera" maxlength="100"
                                :value="old('ncfinanciera', $cargo->ncfinanciera)" :error="$errors->first('ncfinanciera')" optional />
                            <x-ui.text-input name="ncfe" label="NCFE" maxlength="100"
                                :value="old('ncfe', $cargo->ncfe)" :error="$errors->first('ncfe')" optional />
                        </x-ui.grid>
                    </x-ui.stack>

                    <x-ui.text-area name="comentario_back" label="Comentario de back office"
                        :value="old('comentario_back', $cargo->comentario_back)" :error="$errors->first('comentario_back')"
                        rows="4" required />

                    <x-ui.stack direction="horizontal" gap="2" h-align="end" wrap="wrap">
                        <x-ui.button :href="route('notas-cargo.fija.back.index')" variant="secondary">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="primary">Guardar seguimiento</x-ui.button>
                    </x-ui.stack>
                </x-ui.form-layout>
            </form>
        </x-ui.card>
    </x-ui.detail-page>
@endsection
