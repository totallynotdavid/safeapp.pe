@extends('layouts.app')

@section('title', 'Nota de cargo fija #'.$cargo->id_cargo)
@section('page', true)

@section('content')
    <x-ui.detail-page title="Nota de cargo fija #{{ $cargo->id_cargo }}"
        :subtitle="$cargo->cliente?->razon_social"
        :back-href="route('notas-cargo.fija.index')" back-label="Volver a mis notas de cargo">
        <x-slot:metadata>
            <x-ui.text type="supporting" color="secondary">・ RUC {{ $cargo->cliente?->ruc }}</x-ui.text>
            <x-ui.text type="supporting" color="secondary">・</x-ui.text>
            <x-ui.badge>{{ \App\Support\EtiquetasDeDominio::texto($cargo->estado, 'estado') }}</x-ui.badge>
            <x-ui.text type="supporting" color="secondary">・ {{ $cargo->fecha_ingreso?->format('d/m/Y') }}</x-ui.text>
        </x-slot:metadata>

        @include('notas-cargo.fija.partials.detalle', [
            'mostrarSeguimientoEjecutivo' => ! $cargo->esEditable(),
        ])

        @if ($cargo->esEditable())
            <x-ui.card padding="6">
                <form method="POST" action="{{ route('notas-cargo.fija.update', $cargo) }}">
                    @csrf
                    @method('PUT')

                    <x-ui.form-layout>
                        <x-ui.stack direction="vertical" gap="0.5">
                            <x-ui.heading level="2">Actualizar coordinación</x-ui.heading>
                            <x-ui.text type="supporting" color="secondary">Corrija el contacto y la instalación mientras la nota siga pendiente de validación.</x-ui.text>
                        </x-ui.stack>

                        <x-ui.stack direction="vertical" gap="3">
                            <x-ui.stack direction="vertical" gap="0.5">
                                <x-ui.heading level="3">Contacto</x-ui.heading>
                                <x-ui.text type="supporting" color="secondary">Persona con quien se coordinará la atención.</x-ui.text>
                            </x-ui.stack>
                            <x-ui.text-input name="contacto" label="Nombre del contacto" maxlength="100"
                                :value="old('contacto', $cargo->contacto)" :error="$errors->first('contacto')" required />
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
                                <x-ui.heading level="3">Instalación</x-ui.heading>
                                <x-ui.text type="supporting" color="secondary">Tecnología y ubicación que revisará el equipo validador.</x-ui.text>
                            </x-ui.stack>
                            <x-ui.selector name="tecnologia" label="Tecnología" :options="\App\Support\EtiquetasDeDominio::opciones($tecnologias, 'tecnologia')"
                                :value="old('tecnologia', $cargo->tecnologia)" :error="$errors->first('tecnologia')" required />
                            <x-ui.text-input name="direccion" label="Dirección de instalación"
                                :value="old('direccion', $cargo->direccion)" :error="$errors->first('direccion')" required />
                            <x-ui.text-input name="coordenadas" label="Coordenadas" maxlength="100"
                                :value="old('coordenadas', $cargo->coordenadas)" :error="$errors->first('coordenadas')" optional />
                            <x-ui.text-area name="comentario" label="Comentario del ejecutivo"
                                :value="old('comentario', $cargo->comentario)" :error="$errors->first('comentario')" rows="4" optional />
                        </x-ui.stack>

                        <x-ui.stack direction="horizontal" gap="2" h-align="end" wrap="wrap">
                            <x-ui.button :href="route('notas-cargo.fija.index')" variant="secondary">Cancelar</x-ui.button>
                            <x-ui.button type="submit" variant="primary">Guardar cambios</x-ui.button>
                        </x-ui.stack>
                    </x-ui.form-layout>
                </form>
            </x-ui.card>
        @else
            <x-ui.banner status="warning" title="Solicitud cerrada"
                description="Esta nota ya fue validada. La información registrada por el ejecutivo se conserva en modo de solo lectura." />
        @endif
    </x-ui.detail-page>
@endsection
