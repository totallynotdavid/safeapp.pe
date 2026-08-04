@extends('layouts.app')

@section('title', 'Validar nota fija #'.$cargo->id_cargo)
@section('page', true)

@section('content')
    <x-ui.detail-page title="Validar nota fija #{{ $cargo->id_cargo }}" :subtitle="$cargo->cliente?->razon_social"
        :back-href="route('notas-cargo.fija.validacion.index')" back-label="Volver a notas por validar">
        <x-slot:metadata>
            <x-ui.text type="supporting" color="secondary">・ RUC {{ $cargo->cliente?->ruc }}</x-ui.text>
            <x-ui.text type="supporting" color="secondary">・</x-ui.text><x-ui.badge>{{ \App\Support\EtiquetasDeDominio::texto($cargo->validacion, 'validacion') }}</x-ui.badge>
            <x-ui.text type="supporting" color="secondary">・ {{ $cargo->fecha_ingreso?->format('d/m/Y') }}</x-ui.text>
        </x-slot:metadata>

        @include('notas-cargo.fija.partials.detalle')

        <x-ui.card padding="6">
            <form method="POST" action="{{ route('notas-cargo.fija.validacion.update', $cargo) }}">
                @csrf @method('PUT')
                <x-ui.form-layout>
                    <x-ui.stack direction="vertical" gap="0.5">
                        <x-ui.heading level="2">Registrar validación</x-ui.heading>
                        <x-ui.text type="supporting" color="secondary">Emita una decisión y deje el sustento para las siguientes etapas.</x-ui.text>
                    </x-ui.stack>
                    <x-ui.banner status="info" title="Datos del registro protegidos" description="En fija, el equipo validador decide sobre la solicitud completa. Una discrepancia con sus datos se registra como rechazo." />
                    <x-ui.selector name="validacion" label="Decisión" :options="\App\Support\EtiquetasDeDominio::opciones($validaciones, 'validacion')" :value="old('validacion')"
                        :error="$errors->first('validacion')" placeholder="Seleccionar decisión…" required />
                    <x-ui.text-area name="comentario_validador" label="Comentario del validador" :value="old('comentario_validador')"
                        :error="$errors->first('comentario_validador')" rows="4" required />
                    <x-ui.stack direction="horizontal" gap="2" h-align="end" wrap="wrap">
                        <x-ui.button :href="route('notas-cargo.fija.validacion.index')" variant="secondary">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="primary">Guardar validación</x-ui.button>
                    </x-ui.stack>
                </x-ui.form-layout>
            </form>
        </x-ui.card>
    </x-ui.detail-page>
@endsection
