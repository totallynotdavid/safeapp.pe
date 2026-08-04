@extends('layouts.app')

@section('title', 'Validar quiebre #'.$quiebre->id_quiebre)
@section('page', true)

@section('content')
    <x-ui.detail-page title="Validar quiebre #{{ $quiebre->id_quiebre }}"
        :subtitle="$quiebre->cliente->razon_social"
        :back-href="route('quiebres.validacion.index')" back-label="Volver a quiebres por validar">
        <x-slot:metadata>
            <x-ui.text type="supporting" color="secondary">・ RUC {{ $quiebre->cliente->ruc }}</x-ui.text>
            <x-ui.text type="supporting" color="secondary">・</x-ui.text>
            <x-ui.badge>{{ \App\Support\EtiquetasDeDominio::texto($quiebre->estado, 'estado') }}</x-ui.badge>
            <x-ui.text type="supporting" color="secondary">・ {{ $quiebre->fecha_registro?->format('d/m/Y') }}</x-ui.text>
        </x-slot:metadata>

        <x-ui.card padding="6">
            <x-ui.stack direction="vertical" gap="4">
                <x-ui.stack direction="vertical" gap="0.5">
                    <x-ui.heading level="2">Detalle de la avería</x-ui.heading>
                    <x-ui.text type="supporting" color="secondary">Revise el servicio, la incidencia y el ticket antes de emitir una decisión.</x-ui.text>
                </x-ui.stack>

                <x-ui.metadata-list columns="multi">
                    @include('partials.campo', ['label' => 'Fecha de registro', 'value' => $quiebre->fecha_registro?->format('d/m/Y')])
                    @include('partials.campo', ['label' => 'Activación del servicio', 'value' => $quiebre->fecha_activacion?->format('d/m/Y')])
                    @include('partials.campo', ['label' => 'Inicio de avería', 'value' => $quiebre->fecha_inicio_averia?->format('d/m/Y')])
                    @include('partials.campo', ['label' => 'Región', 'value' => $quiebre->tienda?->nombre])
                    @include('partials.campo', ['label' => 'Servicio', 'value' => \App\Support\EtiquetasDeDominio::texto($quiebre->servicio, 'servicio')])
                    @include('partials.campo', ['label' => 'Tipo de avería', 'value' => \App\Support\EtiquetasDeDominio::texto($quiebre->tipo_averia, 'general')])
                    @include('partials.campo', ['label' => 'Problema con el equipo', 'value' => \App\Support\EtiquetasDeDominio::texto($quiebre->problema_equipo, 'si_no')])
                    @include('partials.campo', ['label' => 'Detalle del equipo', 'value' => \App\Support\EtiquetasDeDominio::texto($quiebre->detalle_equipo, 'detalle_equipo')])
                    @include('partials.campo', ['label' => 'Back', 'value' => $quiebre->back])
                </x-ui.metadata-list>

                <x-ui.divider />

                <x-ui.stack direction="vertical" gap="0.5">
                    <x-ui.heading level="3">Ticket de atención</x-ui.heading>
                    <x-ui.text type="supporting" color="secondary">Referencia de atención informada al registrar el quiebre.</x-ui.text>
                </x-ui.stack>
                <x-ui.metadata-list columns="multi">
                    @include('partials.campo', ['label' => 'Ticket requerido', 'value' => \App\Support\EtiquetasDeDominio::texto($quiebre->ticket_atencion, 'si_no')])
                    @include('partials.campo', ['label' => 'Número de ticket', 'value' => $quiebre->numero_ticket])
                    @include('partials.campo', ['label' => 'Fecha del ticket', 'value' => $quiebre->fecha_ticket_atencion?->format('d/m/Y')])
                </x-ui.metadata-list>
            </x-ui.stack>
        </x-ui.card>

        <x-ui.card padding="6">
            <x-ui.stack direction="vertical" gap="4">
                <x-ui.stack direction="vertical" gap="0.5">
                    <x-ui.heading level="2">Seguimiento del ejecutivo</x-ui.heading>
                    <x-ui.text type="supporting" color="secondary">Alcance y contactos disponibles para coordinar la atención.</x-ui.text>
                </x-ui.stack>

                <x-ui.metadata-list columns="multi">
                    @include('partials.campo', ['label' => 'Ejecutivo', 'value' => $quiebre->ejecutivo?->personal])
                    @include('partials.campo', ['label' => 'Líneas con avería', 'value' => $quiebre->numero_problema])
                    @include('partials.campo', ['label' => 'Contacto principal', 'value' => $quiebre->contacto1])
                    @include('partials.campo', ['label' => 'Celular principal', 'value' => $quiebre->celular1])
                    @include('partials.campo', ['label' => 'Correo principal', 'value' => $quiebre->correo1])
                    @include('partials.campo', ['label' => 'Contacto adicional', 'value' => $quiebre->contacto2])
                    @include('partials.campo', ['label' => 'Celular adicional', 'value' => $quiebre->celular2])
                    @include('partials.campo', ['label' => 'Correo adicional', 'value' => $quiebre->correo2])
                </x-ui.metadata-list>

                <x-ui.divider />
                <x-ui.metadata-list>
                    @include('partials.campo', ['label' => 'Comentario del ejecutivo', 'value' => $quiebre->comentario_ejecutivo])
                </x-ui.metadata-list>
            </x-ui.stack>
        </x-ui.card>

        <x-ui.card padding="6">
            <form method="POST" action="{{ route('quiebres.validacion.update', $quiebre) }}">
                @csrf
                @method('PUT')

                <x-ui.form-layout>
                    <x-ui.stack direction="vertical" gap="0.5">
                        <x-ui.heading level="2">Registrar validación</x-ui.heading>
                        <x-ui.text type="supporting" color="secondary">Defina el resultado de la revisión y vincúlelo con el caso de Salesforce.</x-ui.text>
                    </x-ui.stack>

                    <x-ui.grid min-width="220" repeat="fit" gap="3">
                        <x-ui.selector name="estado" label="Estado" :options="\App\Support\EtiquetasDeDominio::opciones($estados, 'estado')"
                            :value="old('estado', $quiebre->estado)" :error="$errors->first('estado')" required />
                        <x-ui.text-input name="caso_sf" label="Caso Salesforce" maxlength="50"
                            :value="old('caso_sf', $quiebre->caso_sf)" :error="$errors->first('caso_sf')" required />
                    </x-ui.grid>

                    <x-ui.text-area name="comentario_validador" label="Comentario del validador"
                        :value="old('comentario_validador', $quiebre->comentario_validador)"
                        :error="$errors->first('comentario_validador')" rows="4" optional />

                    <x-ui.stack direction="horizontal" gap="2" h-align="end" wrap="wrap">
                        <x-ui.button :href="route('quiebres.validacion.index')" variant="secondary">Cancelar</x-ui.button>
                        <x-ui.button type="submit" variant="primary">Guardar validación</x-ui.button>
                    </x-ui.stack>
                </x-ui.form-layout>
            </form>
        </x-ui.card>
    </x-ui.detail-page>
@endsection
