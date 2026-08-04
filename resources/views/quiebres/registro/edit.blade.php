@extends('layouts.app')

@section('title', 'Quiebre #'.$quiebre->id_quiebre)
@section('page', true)

@section('content')
    <x-ui.detail-page title="Quiebre #{{ $quiebre->id_quiebre }}"
        :subtitle="$quiebre->cliente->razon_social"
        :back-href="route('quiebres.registro.index')" back-label="Volver a mis quiebres">
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
                    <x-ui.text type="supporting" color="secondary">Datos registrados al abrir el quiebre. Esta información no cambia durante el seguimiento.</x-ui.text>
                </x-ui.stack>

                <x-ui.metadata-list columns="multi">
                    @include('partials.campo', ['label' => 'Fecha de registro', 'value' => $quiebre->fecha_registro?->format('d/m/Y')])
                    @include('partials.campo', ['label' => 'Activación del servicio', 'value' => $quiebre->fecha_activacion?->format('d/m/Y')])
                    @include('partials.campo', ['label' => 'Inicio de avería', 'value' => $quiebre->fecha_inicio_averia?->format('d/m/Y')])
                    @include('partials.campo', ['label' => 'Región', 'value' => $quiebre->tienda?->nombre])
                    @include('partials.campo', ['label' => 'Servicio', 'value' => \App\Support\EtiquetasDeDominio::texto($quiebre->servicio, 'servicio')])
                    @include('partials.campo', ['label' => 'Tipo de avería', 'value' => \App\Support\EtiquetasDeDominio::texto($tiposAveria[$quiebre->tipo_averia] ?? $quiebre->tipo_averia, 'general')])
                    @include('partials.campo', ['label' => 'Problema con el equipo', 'value' => \App\Support\EtiquetasDeDominio::texto($quiebre->problema_equipo, 'si_no')])
                    @include('partials.campo', ['label' => 'Detalle del equipo', 'value' => \App\Support\EtiquetasDeDominio::texto($quiebre->detalle_equipo, 'detalle_equipo')])
                    @include('partials.campo', ['label' => 'Back', 'value' => $quiebre->back])
                </x-ui.metadata-list>

                <x-ui.divider />

                <x-ui.stack direction="vertical" gap="0.5">
                    <x-ui.heading level="3">Ticket de atención</x-ui.heading>
                    <x-ui.text type="supporting" color="secondary">Información exigida según la antigüedad del servicio.</x-ui.text>
                </x-ui.stack>
                <x-ui.metadata-list columns="multi">
                    @include('partials.campo', ['label' => 'Ticket requerido', 'value' => \App\Support\EtiquetasDeDominio::texto($quiebre->ticket_atencion, 'si_no')])
                    @include('partials.campo', ['label' => 'Número de ticket', 'value' => $quiebre->numero_ticket])
                    @include('partials.campo', ['label' => 'Fecha del ticket', 'value' => $quiebre->fecha_ticket_atencion?->format('d/m/Y')])
                </x-ui.metadata-list>
            </x-ui.stack>
        </x-ui.card>

        @if ($quiebre->esEditable())
            <x-ui.card padding="6">
                <form method="POST" action="{{ route('quiebres.registro.update', $quiebre) }}">
                    @csrf
                    @method('PUT')

                    <x-ui.form-layout>
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="2">Actualizar seguimiento</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Actualice el alcance, los contactos y el comentario mientras el quiebre siga abierto.</x-ui.text>
                        </x-ui.stack>

                        <x-ui.text-input name="numero_problema" label="Líneas con avería" type="number" min="1"
                            :value="old('numero_problema', $quiebre->numero_problema)"
                            :error="$errors->first('numero_problema')" required />

                        <x-ui.stack direction="vertical" gap="3">
                            <x-ui.stack direction="vertical" gap="0.5">
                                <x-ui.heading level="3">Contacto principal</x-ui.heading>
                                <x-ui.text type="supporting" color="secondary">Persona responsable de coordinar la atención.</x-ui.text>
                            </x-ui.stack>
                            <x-ui.grid min-width="220" repeat="fit" gap="3">
                                <x-ui.text-input name="contacto1" label="Nombre" :value="old('contacto1', $quiebre->contacto1)"
                                    :error="$errors->first('contacto1')" required />
                                <x-ui.text-input name="celular1" label="Celular" :value="old('celular1', $quiebre->celular1)"
                                    :error="$errors->first('celular1')" required />
                            </x-ui.grid>
                            <x-ui.text-input name="correo1" label="Correo" type="email"
                                :value="old('correo1', $quiebre->correo1)" :error="$errors->first('correo1')" required />
                        </x-ui.stack>

                        <x-ui.divider />

                        <x-ui.stack direction="vertical" gap="3">
                            <x-ui.stack direction="vertical" gap="0.5">
                                <x-ui.heading level="3">Contacto adicional</x-ui.heading>
                                <x-ui.text type="supporting" color="secondary">Regístrelo solo cuando exista otro punto de coordinación.</x-ui.text>
                            </x-ui.stack>
                            <x-ui.grid min-width="220" repeat="fit" gap="3">
                                <x-ui.text-input name="contacto2" label="Nombre" :value="old('contacto2', $quiebre->contacto2)"
                                    :error="$errors->first('contacto2')" optional />
                                <x-ui.text-input name="celular2" label="Celular" :value="old('celular2', $quiebre->celular2)"
                                    :error="$errors->first('celular2')" optional />
                            </x-ui.grid>
                            <x-ui.text-input name="correo2" label="Correo" type="email"
                                :value="old('correo2', $quiebre->correo2)" :error="$errors->first('correo2')" optional />
                        </x-ui.stack>

                        <x-ui.text-area name="comentario_ejecutivo" label="Comentario del ejecutivo"
                            :value="old('comentario_ejecutivo', $quiebre->comentario_ejecutivo)"
                            :error="$errors->first('comentario_ejecutivo')" rows="4" optional />

                        <x-ui.stack direction="horizontal" gap="2" h-align="end" wrap="wrap">
                            <x-ui.button :href="route('quiebres.registro.index')" variant="secondary">Cancelar</x-ui.button>
                            <x-ui.button type="submit" variant="primary">Guardar cambios</x-ui.button>
                        </x-ui.stack>
                    </x-ui.form-layout>
                </form>
            </x-ui.card>
        @else
            <x-ui.banner status="warning" title="Seguimiento cerrado"
                description="Este quiebre está en estado {{ \App\Support\EtiquetasDeDominio::texto($quiebre->estado, 'estado') }}. Sus datos de seguimiento ya no pueden modificarse." />

            <x-ui.card padding="6">
                <x-ui.stack direction="vertical" gap="4">
                    <x-ui.stack direction="vertical" gap="0.5">
                        <x-ui.heading level="2">Seguimiento del ejecutivo</x-ui.heading>
                        <x-ui.text type="supporting" color="secondary">Información final conservada en modo de solo lectura.</x-ui.text>
                    </x-ui.stack>

                    <x-ui.metadata-list columns="multi">
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
        @endif

        <x-ui.card padding="6">
            <x-ui.stack direction="vertical" gap="4">
                <x-ui.stack direction="vertical" gap="0.5">
                    <x-ui.heading level="2">Validación</x-ui.heading>
                    <x-ui.text type="supporting" color="secondary">Decisión y referencia registradas por el equipo validador.</x-ui.text>
                </x-ui.stack>

                <x-ui.metadata-list columns="multi">
                    @include('partials.campo', ['label' => 'Validador', 'value' => $quiebre->validador?->personal])
                    @include('partials.campo', ['label' => 'Fecha de validación', 'value' => $quiebre->fecha_validacion?->format('d/m/Y')])
                    @include('partials.campo', ['label' => 'Caso Salesforce', 'value' => $quiebre->caso_sf])
                </x-ui.metadata-list>

                <x-ui.divider />
                <x-ui.metadata-list>
                    @include('partials.campo', ['label' => 'Comentario del validador', 'value' => $quiebre->comentario_validador])
                </x-ui.metadata-list>
            </x-ui.stack>
        </x-ui.card>
    </x-ui.detail-page>
@endsection
