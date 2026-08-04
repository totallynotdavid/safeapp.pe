@extends('layouts.app')

@section('title', 'Validar nota móvil #'.$cargo->id_cargo)
@section('page', true)

@section('content')
    <x-ui.detail-page title="Validar nota móvil #{{ $cargo->id_cargo }}"
        :subtitle="$cargo->cliente?->razon_social"
        :back-href="route('notas-cargo.movil.validacion.index')" back-label="Volver a notas por validar">
        <x-slot:metadata>
            <x-ui.text type="supporting" color="secondary">・ RUC {{ $cargo->cliente?->ruc }}</x-ui.text>
            <x-ui.text type="supporting" color="secondary">・</x-ui.text>
            <x-ui.badge>{{ \App\Support\EtiquetasDeDominio::texto($cargo->validacion, 'validacion') }}</x-ui.badge>
            <x-ui.text type="supporting" color="secondary">・ {{ $cargo->fecha_ingreso?->format('d/m/Y') }}</x-ui.text>
        </x-slot:metadata>

        @include('notas-cargo.movil.partials.detalle')

        <x-ui.card padding="6">
            <form method="POST" action="{{ route('notas-cargo.movil.validacion.update', $cargo) }}">
                @csrf
                @method('PUT')

                <x-ui.form-layout>
                    <x-ui.stack direction="vertical" gap="0.5">
                        <x-ui.heading level="2">Registrar validación</x-ui.heading>
                        <x-ui.text type="supporting" color="secondary">Confirme los valores revisados, emita una decisión y deje el sustento para las siguientes etapas.</x-ui.text>
                    </x-ui.stack>

                    {{-- The validador owns only these two correctable figures,
                         the decision and its comment. Identity/date are server-owned. --}}
                    <x-ui.stack direction="vertical" gap="3">
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="3">Valores revisados</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Corrija las cifras solo cuando la evidencia de la oportunidad lo requiera.</x-ui.text>
                        </x-ui.stack>
                        <x-ui.grid min-width="180" repeat="fit" gap="3">
                            <x-ui.text-input name="q_lineas" label="Líneas" type="number" min="1"
                                :value="old('q_lineas', $cargo->q_lineas)" :error="$errors->first('q_lineas')" required />
                            <x-ui.text-input name="cargo_fijo" label="Cargo fijo" type="number" min="0" step="0.01"
                                :value="old('cargo_fijo', $cargo->cargo_fijo)" :error="$errors->first('cargo_fijo')" required />
                        </x-ui.grid>
                    </x-ui.stack>

                    <x-ui.divider />

                    <x-ui.selector name="validacion" label="Decisión" :options="\App\Support\EtiquetasDeDominio::opciones($validaciones, 'validacion')"
                        :value="old('validacion')" :error="$errors->first('validacion')"
                        placeholder="Seleccionar decisión…" required />
                    <x-ui.text-area name="comentario_validador" label="Comentario del validador"
                        :value="old('comentario_validador')" :error="$errors->first('comentario_validador')"
                        rows="4" required />

                    <x-ui.stack direction="horizontal" gap="2" h-align="end" wrap="wrap">
                        <x-ui.button :href="route('notas-cargo.movil.validacion.index')" variant="secondary">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="primary">Guardar validación</x-ui.button>
                    </x-ui.stack>
                </x-ui.form-layout>
            </form>
        </x-ui.card>
    </x-ui.detail-page>
@endsection
