@extends('layouts.app')

@section('title', 'Liquidación móvil #'.$cargo->id_cargo)
@section('page', true)

@section('content')
    <x-ui.detail-page title="Liquidación móvil #{{ $cargo->id_cargo }}"
        :subtitle="$cargo->cliente?->razon_social"
        :back-href="route('liquidaciones.index')" back-label="Volver a notas por liquidar">
        <x-slot:metadata>
            <x-ui.text type="supporting" color="secondary">・ RUC {{ $cargo->cliente?->ruc }}</x-ui.text>
            <x-ui.text type="supporting" color="secondary">・</x-ui.text>
            <x-ui.badge>{{ \App\Support\EtiquetasDeDominio::texto($cargo->estado, 'estado') }}</x-ui.badge>
            <x-ui.text type="supporting" color="secondary">・ Actualizado {{ $cargo->fecha_actualizacion?->format('d/m/Y') }}</x-ui.text>
        </x-slot:metadata>

        @include('notas-cargo.movil.partials.detalle')

        <x-ui.card padding="6">
            <form method="POST" action="{{ route('liquidaciones.update', $cargo) }}">
                @csrf
                @method('PUT')

                <x-ui.form-layout>
                    <x-ui.stack direction="vertical" gap="0.5">
                        <x-ui.heading level="2">Registrar liquidación</x-ui.heading>
                        <x-ui.text type="supporting" color="secondary">Complete el resultado económico y la fecha efectiva de liquidación.</x-ui.text>
                    </x-ui.stack>

                    <x-ui.stack direction="vertical" gap="3">
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="3">Resultado económico</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Importes reportados para la comisión y el descuento de la nota de cargo.</x-ui.text>
                        </x-ui.stack>
                        <x-ui.grid min-width="180" repeat="fit" gap="3">
                            <x-ui.text-input name="comision" label="Comisión" maxlength="50"
                                :value="old('comision', $cargo->comision)" :error="$errors->first('comision')" required />
                            <x-ui.text-input name="descuento" label="Descuento" maxlength="50"
                                :value="old('descuento', $cargo->descuento)" :error="$errors->first('descuento')" required />
                        </x-ui.grid>
                    </x-ui.stack>

                    <x-ui.divider />

                    <x-ui.stack direction="vertical" gap="3">
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="3">Cierre de la liquidación</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Registre el motivo observado y la fecha en que se completa el proceso.</x-ui.text>
                        </x-ui.stack>
                        <x-ui.grid min-width="180" repeat="fit" gap="3">
                            <x-ui.selector name="observacion" label="Observación" :options="\App\Support\EtiquetasDeDominio::opciones($observaciones, 'observacion')"
                                :value="old('observacion', $cargo->observacion)" :error="$errors->first('observacion')" required />
                            <x-ui.date-input name="fecha_liquidacion" label="Fecha de liquidación"
                                :value="old('fecha_liquidacion', $cargo->fecha_liquidacion?->format('Y-m-d'))"
                                :error="$errors->first('fecha_liquidacion')" required />
                        </x-ui.grid>
                    </x-ui.stack>

                    <x-ui.text-area name="comentario_liquidador" label="Comentario del liquidador"
                        :value="old('comentario_liquidador', $cargo->comentario_liquidador)"
                        :error="$errors->first('comentario_liquidador')" rows="4" optional />

                    <x-ui.stack direction="horizontal" gap="2" h-align="end" wrap="wrap">
                        <x-ui.button :href="route('liquidaciones.index')" variant="secondary">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="primary">Guardar liquidación</x-ui.button>
                    </x-ui.stack>
                </x-ui.form-layout>
            </form>
        </x-ui.card>
    </x-ui.detail-page>
@endsection
