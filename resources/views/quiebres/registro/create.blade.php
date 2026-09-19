@extends('layouts.app')

@section('title', 'Nuevo quiebre')
@section('page', true)

@section('content')
    @php
        $tiendaOptions = $tiendas->map(fn ($tienda) => [
            'value' => $tienda->id_tienda,
            'label' => $tienda->nombre,
        ])->all();
        $tipoAveriaOptions = collect($tiposAveria)->map(
            fn ($label, $value) => ['value' => $value, 'label' => \App\Support\EtiquetasDeDominio::texto($label, 'general')],
        )->values()->all();
        $servicioOptions = \App\Support\EtiquetasDeDominio::opciones($servicios, 'servicio');
        $problemaEquipoOptions = \App\Support\EtiquetasDeDominio::opciones(['NO', 'SI'], 'si_no');
        $detalleEquipoOptions = \App\Support\EtiquetasDeDominio::opciones($detalles, 'detalle_equipo');
        $problemaEquipo = old('problema_equipo', 'NO');
        $requiereTicket = false;

        if (old('fecha_activacion')) {
            try {
                $requiereTicket = \App\Models\Quiebre::requiereTicket(
                    \Illuminate\Support\Carbon::parse(old('fecha_activacion')),
                );
            } catch (\Throwable) {
                // The DateInput owns the invalid value and attached server error.
            }
        }
    @endphp

    <x-ui.table-page title="Nuevo quiebre" content-width="1200">
        @include('partials.buscar-cliente', ['ruta' => 'quiebres.registro.create', 'ruc' => $ruc])

        @if ($ruc && ! $cliente)
            <x-ui.banner status="warning" title="Cliente no disponible"
                description="Registre la identidad para continuar con este quiebre." />
            @include('partials.cliente-identidad-dialog', [
                'rutaIdentidad' => 'quiebres.registro.cliente.store',
                'ruc' => $ruc,
            ])
        @endif

        @if ($cliente)
            <x-ui.grid min-width="320" repeat="fit" gap="10" align="start">
                <x-ui.stack direction="vertical" gap="6">
                    <x-ui.stack direction="vertical" gap="3">
                        <x-ui.heading level="2">{{ $cliente->razon_social }}</x-ui.heading>
                        <x-ui.text type="body" color="secondary">
                            Registre la avería contra la identidad resuelta. El cliente, responsable y estado del flujo se mantienen protegidos por el sistema.
                        </x-ui.text>
                    </x-ui.stack>

                    <x-ui.metadata-list>
                        @include('partials.campo', ['label' => 'RUC', 'value' => $cliente->ruc])
                        @include('partials.campo', ['label' => 'Razón social', 'value' => $cliente->razon_social])
                        @include('partials.campo', ['label' => 'Zonal del cliente', 'value' => $cliente->zonal])
                    </x-ui.metadata-list>

                    <x-ui.banner status="info" title="Cuándo se solicita un ticket"
                        description="Si el servicio tiene 90 días o más desde su activación, el número y la fecha del ticket son obligatorios." />
                </x-ui.stack>

                <x-ui.card padding="8">
                    <form method="POST" action="{{ route('quiebres.registro.store') }}">
                        @csrf
                        {{-- The resolved client is the only identity accepted by the form. --}}
                        <input type="hidden" name="id_cliente" value="{{ $cliente->id_cliente }}">

                        <x-ui.form-layout>
                            <x-ui.stack direction="vertical" gap="0.5">
                                <x-ui.heading level="2">Datos de la avería</x-ui.heading>
                                <x-ui.text type="supporting" color="secondary">Complete la clasificación, fechas y alcance reportado.</x-ui.text>
                            </x-ui.stack>

                            @if ($errors->has('id_cliente'))
                                <x-ui.banner status="error" title="No se pudo identificar al cliente"
                                    :description="$errors->first('id_cliente')" />
                            @endif

                            <x-ui.grid min-width="180" repeat="fit" gap="3">
                                <x-ui.selector name="zonal_telefonica" label="Región" :options="$tiendaOptions"
                                    :value="old('zonal_telefonica')" :error="$errors->first('zonal_telefonica')" required />
                                <x-ui.selector name="servicio" label="Servicio" :options="$servicioOptions"
                                    :value="old('servicio')" :error="$errors->first('servicio')" required />
                            </x-ui.grid>

                            <x-ui.selector name="tipo_averia" label="Tipo de avería" :options="$tipoAveriaOptions"
                                :value="old('tipo_averia')" :error="$errors->first('tipo_averia')" required />

                            <x-ui.grid min-width="180" repeat="fit" gap="3">
                                <x-ui.date-input name="fecha_activacion" label="Activación del servicio"
                                    :value="old('fecha_activacion')" :error="$errors->first('fecha_activacion')" required />
                                <x-ui.date-input name="fecha_inicio_averia" label="Inicio de avería"
                                    :value="old('fecha_inicio_averia')" :error="$errors->first('fecha_inicio_averia')" optional has-clear />
                            </x-ui.grid>

                            <x-ui.grid min-width="180" repeat="fit" gap="3">
                                <x-ui.text-input name="numero_problema" label="Líneas con avería" type="number" min="1"
                                    :value="old('numero_problema')" :error="$errors->first('numero_problema')" required />
                                <x-ui.selector name="problema_equipo" label="Problema con el equipo" :options="$problemaEquipoOptions"
                                    :value="$problemaEquipo" :error="$errors->first('problema_equipo')" required />
                            </x-ui.grid>

                            <x-ui.grid min-width="180" repeat="fit" gap="3">
                                <x-ui.selector name="detalle_equipo" label="Detalle del equipo" :options="$detalleEquipoOptions"
                                    :value="old('detalle_equipo')" :error="$errors->first('detalle_equipo')"
                                    :required="$problemaEquipo === 'SI'" :optional="$problemaEquipo !== 'SI'" />
                                <x-ui.text-input name="back" label="Back" :value="old('back')"
                                    :error="$errors->first('back')" optional />
                            </x-ui.grid>

                            <x-ui.divider />

                            <x-ui.stack direction="vertical" gap="0.5">
                                <x-ui.heading level="3">Ticket de atención</x-ui.heading>
                                <x-ui.text type="supporting" color="secondary">
                                    {{ $requiereTicket ? 'Obligatorio por la antigüedad del servicio.' : 'Obligatorio cuando la activación tiene 90 días o más.' }}
                                </x-ui.text>
                            </x-ui.stack>

                            <x-ui.grid min-width="180" repeat="fit" gap="3">
                                <x-ui.text-input name="numero_ticket" label="Número de ticket"
                                    :value="old('numero_ticket')" :error="$errors->first('numero_ticket')"
                                    :required="$requiereTicket" :optional="! $requiereTicket" />
                                <x-ui.date-input name="fecha_ticket_atencion" label="Fecha de ticket"
                                    :value="old('fecha_ticket_atencion')" :error="$errors->first('fecha_ticket_atencion')"
                                    :required="$requiereTicket" :optional="! $requiereTicket" has-clear />
                            </x-ui.grid>

                            <x-ui.divider />

                            <x-ui.stack direction="vertical" gap="0.5">
                                <x-ui.heading level="3">Contactos</x-ui.heading>
                                <x-ui.text type="supporting" color="secondary">El primer contacto es obligatorio; agregue un segundo cuando esté disponible.</x-ui.text>
                            </x-ui.stack>

                            <x-ui.grid min-width="180" repeat="fit" gap="3">
                                <x-ui.text-input name="contacto1" label="Contacto 1" :value="old('contacto1')"
                                    :error="$errors->first('contacto1')" required />
                                <x-ui.text-input name="celular1" label="Celular 1" :value="old('celular1')"
                                    :error="$errors->first('celular1')" required />
                            </x-ui.grid>
                            <x-ui.text-input name="correo1" label="Correo 1" type="email" :value="old('correo1')"
                                :error="$errors->first('correo1')" required />

                            <x-ui.grid min-width="180" repeat="fit" gap="3">
                                <x-ui.text-input name="contacto2" label="Contacto 2" :value="old('contacto2')"
                                    :error="$errors->first('contacto2')" optional />
                                <x-ui.text-input name="celular2" label="Celular 2" :value="old('celular2')"
                                    :error="$errors->first('celular2')" optional />
                            </x-ui.grid>
                            <x-ui.text-input name="correo2" label="Correo 2" type="email" :value="old('correo2')"
                                :error="$errors->first('correo2')" optional />

                            <x-ui.text-area name="comentario_ejecutivo" label="Comentario del ejecutivo"
                                :value="old('comentario_ejecutivo')" :error="$errors->first('comentario_ejecutivo')" rows="4" optional />

                            <x-ui.stack direction="horizontal" gap="2" wrap="wrap">
                                <x-ui.button type="submit" variant="primary">Registrar quiebre</x-ui.button>
                                <x-ui.button href="{{ route('quiebres.registro.index') }}" variant="secondary">Cancelar</x-ui.button>
                            </x-ui.stack>
                        </x-ui.form-layout>
                    </form>
                </x-ui.card>
            </x-ui.grid>
        @endif
    </x-ui.table-page>
@endsection
