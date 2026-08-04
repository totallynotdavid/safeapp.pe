@extends('layouts.app')

@section('title', 'Nueva nota de cargo móvil')
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
        $nivelOptions = collect($niveles)->map(fn ($nivel) => [
            'value' => $nivel,
            'label' => $nivel.' %',
        ])->all();
        $modalidadOptions = \App\Support\EtiquetasDeDominio::opciones($modalidades, 'modalidad');
        $tipoProductoOptions = \App\Support\EtiquetasDeDominio::opciones($tiposProducto, 'tipo_producto');
        $etapaOptions = \App\Support\EtiquetasDeDominio::opciones($etapas, 'etapa');
    @endphp

    <x-ui.table-page title="Nueva nota de cargo móvil" content-width="1200">
        @include('partials.buscar-cliente', ['ruta' => 'notas-cargo.movil.create', 'ruc' => $ruc])

        @if ($ruc && ! $cliente)
            <x-ui.banner status="warning" title="Cliente no disponible"
                description="Registre la identidad para continuar con esta nota de cargo." />
            @include('partials.cliente-identidad-dialog', [
                'rutaIdentidad' => 'notas-cargo.movil.cliente.store',
                'ruc' => $ruc,
            ])
        @endif

        @if ($cliente)
            <x-ui.grid min-width="320" repeat="fit" gap="10" align="start">
                <x-ui.stack direction="vertical" gap="6">
                    <x-ui.stack direction="vertical" gap="3">
                        <x-ui.heading level="2">{{ $cliente->razon_social }}</x-ui.heading>
                        <x-ui.text type="body" color="secondary">
                            Registre la oportunidad móvil contra la identidad resuelta. El propietario, supervisor, fecha y estado son asignados por el sistema.
                        </x-ui.text>
                    </x-ui.stack>

                    <x-ui.metadata-list>
                        @include('partials.campo', ['label' => 'RUC', 'value' => $cliente->ruc])
                        @include('partials.campo', ['label' => 'Razón social', 'value' => $cliente->razon_social])
                        @include('partials.campo', ['label' => 'Zonal del cliente', 'value' => $cliente->zonal])
                    </x-ui.metadata-list>

                    <x-ui.banner status="info" title="Datos protegidos por el flujo"
                        description="La identidad del cliente se toma de la búsqueda. El ejecutivo responsable, supervisor y fecha de ingreso nunca se envían desde este formulario." />
                </x-ui.stack>

                <x-ui.card padding="8">
                    <form method="POST" action="{{ route('notas-cargo.movil.store') }}">
                        @csrf
                        <input type="hidden" name="id_cliente" value="{{ $cliente->id_cliente }}">

                        <x-ui.form-layout>
                            <x-ui.stack direction="vertical" gap="0.5">
                                <x-ui.heading level="2">Datos de la oportunidad</x-ui.heading>
                                <x-ui.text type="supporting" color="secondary">Complete el servicio ofrecido, el contacto y el avance de la negociación.</x-ui.text>
                            </x-ui.stack>

                            @if ($errors->has('id_cliente'))
                                <x-ui.banner status="error" title="No se pudo identificar al cliente"
                                    :description="$errors->first('id_cliente')" />
                            @endif

                            <x-ui.stack direction="vertical" gap="3">
                                <x-ui.stack direction="vertical" gap="0.5">
                                    <x-ui.heading level="3">Servicio móvil</x-ui.heading>
                                    <x-ui.text type="supporting" color="secondary">Alcance económico y clasificación de la propuesta.</x-ui.text>
                                </x-ui.stack>

                                <x-ui.grid min-width="180" repeat="fit" gap="3">
                                    <x-ui.text-input name="q_lineas" label="Líneas" type="number" min="1"
                                        :value="old('q_lineas')" :error="$errors->first('q_lineas')" required />
                                    <x-ui.text-input name="cargo_fijo" label="Cargo fijo" type="number" min="0" step="0.01"
                                        :value="old('cargo_fijo')" :error="$errors->first('cargo_fijo')" required />
                                </x-ui.grid>

                                <x-ui.grid min-width="180" repeat="fit" gap="3">
                                    <x-ui.selector name="modalidad" label="Modalidad" :options="$modalidadOptions"
                                        :value="old('modalidad')" :error="$errors->first('modalidad')" required />
                                    <x-ui.selector name="tipo_producto" label="Tipo de producto" :options="$tipoProductoOptions"
                                        :value="old('tipo_producto')" :error="$errors->first('tipo_producto')" required />
                                </x-ui.grid>

                                <x-ui.grid min-width="180" repeat="fit" gap="3">
                                    <x-ui.selector name="zonal" label="Región" :options="$regionOptions"
                                        :value="old('zonal')" :error="$errors->first('zonal')" required />
                                    <x-ui.selector name="id_cartera" label="Ejecutivo TDP" :options="$carteraOptions"
                                        :value="old('id_cartera', '')" :error="$errors->first('id_cartera')" optional />
                                </x-ui.grid>
                            </x-ui.stack>

                            <x-ui.divider />

                            <x-ui.stack direction="vertical" gap="3">
                                <x-ui.stack direction="vertical" gap="0.5">
                                    <x-ui.heading level="3">Contacto</x-ui.heading>
                                    <x-ui.text type="supporting" color="secondary">Persona con quien se coordina la oportunidad.</x-ui.text>
                                </x-ui.stack>

                                <x-ui.text-input name="contacto" label="Nombre del contacto" maxlength="100"
                                    :value="old('contacto')" :error="$errors->first('contacto')" required />
                                <x-ui.grid min-width="180" repeat="fit" gap="3">
                                    <x-ui.text-input name="telefono1" label="Teléfono principal" maxlength="50"
                                        :value="old('telefono1')" :error="$errors->first('telefono1')" required />
                                    <x-ui.text-input name="telefono2" label="Teléfono adicional" maxlength="50"
                                        :value="old('telefono2')" :error="$errors->first('telefono2')" optional />
                                </x-ui.grid>
                            </x-ui.stack>

                            <x-ui.divider />

                            <x-ui.stack direction="vertical" gap="3">
                                <x-ui.stack direction="vertical" gap="0.5">
                                    <x-ui.heading level="3">Negociación</x-ui.heading>
                                    <x-ui.text type="supporting" color="secondary">Probabilidad, fecha objetivo y fase comercial actual.</x-ui.text>
                                </x-ui.stack>

                                <x-ui.grid min-width="180" repeat="fit" gap="3">
                                    <x-ui.selector name="nivel_negociacion" label="Probabilidad de cierre" :options="$nivelOptions"
                                        :value="old('nivel_negociacion')" :error="$errors->first('nivel_negociacion')" required />
                                    <x-ui.date-input name="fecha_estimada" label="Fecha estimada de cierre"
                                        :value="old('fecha_estimada')" :error="$errors->first('fecha_estimada')" required />
                                    <x-ui.selector name="etapa" label="Fase" :options="$etapaOptions"
                                        :value="old('etapa')" :error="$errors->first('etapa')" required />
                                </x-ui.grid>

                                <x-ui.text-input name="oportunidad" label="Oportunidad" maxlength="100"
                                    :value="old('oportunidad')" :error="$errors->first('oportunidad')" optional />
                                <x-ui.text-area name="comentario_ejecutivo" label="Comentario del ejecutivo"
                                    :value="old('comentario_ejecutivo')" :error="$errors->first('comentario_ejecutivo')" rows="4" optional />
                            </x-ui.stack>

                            <x-ui.stack direction="horizontal" gap="2" wrap="wrap">
                                <x-ui.button type="submit" variant="primary">Registrar nota de cargo</x-ui.button>
                                <x-ui.button :href="route('notas-cargo.movil.index')" variant="secondary">Cancelar</x-ui.button>
                            </x-ui.stack>
                        </x-ui.form-layout>
                    </form>
                </x-ui.card>
            </x-ui.grid>
        @endif
    </x-ui.table-page>
@endsection
