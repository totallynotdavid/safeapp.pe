@extends('layouts.app')

@section('title', 'Nueva nota de cargo fija')
@section('page', true)

@section('content')
    @php
        $regionOptions = $regiones->map(fn ($region) => [
            'value' => $region->id_tienda,
            'label' => $region->nombre,
        ])->all();
        $productOptions = collect($catalogo)->map(fn ($productos, $subtipo) => [
            'title' => $subtipo,
            'options' => collect($productos)->map(fn ($producto) => [
                'value' => $producto->id,
                'label' => $producto->descripcion.' — S/ '.number_format((float) $producto->cargo_fijo, 2),
            ])->values()->all(),
        ])->values()->all();
        $modalidadOptions = \App\Support\EtiquetasDeDominio::opciones($modalidades, 'modalidad');
        $tecnologiaOptions = \App\Support\EtiquetasDeDominio::opciones($tecnologias, 'tecnologia');
    @endphp

    <x-ui.table-page title="Nueva nota de cargo fija" content-width="1200">
        @include('partials.buscar-cliente', ['ruta' => 'notas-cargo.fija.create', 'ruc' => $ruc])

        @if ($ruc && ! $cliente)
            <x-ui.banner status="warning" title="Cliente no disponible"
                description="Registre la identidad para continuar con esta nota de cargo." />
            @include('partials.cliente-identidad-dialog', [
                'rutaIdentidad' => 'notas-cargo.fija.cliente.store',
                'ruc' => $ruc,
            ])
        @endif

        @if ($cliente)
            <x-ui.grid min-width="320" repeat="fit" gap="10" align="start">
                <x-ui.stack direction="vertical" gap="6">
                    <x-ui.stack direction="vertical" gap="3">
                        <x-ui.heading level="2">{{ $cliente->razon_social }}</x-ui.heading>
                        <x-ui.text type="body" color="secondary">
                            Registre el servicio fijo contra la identidad resuelta. El propietario, supervisor, fecha y estado son asignados por el sistema.
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
                    <form method="POST" action="{{ route('notas-cargo.fija.store') }}">
                        @csrf
                        <input type="hidden" name="id_cliente" value="{{ $cliente->id_cliente }}">

                        <x-ui.form-layout>
                            <x-ui.stack direction="vertical" gap="0.5">
                                <x-ui.heading level="2">Datos de la solicitud</x-ui.heading>
                                <x-ui.text type="supporting" color="secondary">Complete el servicio vendido, el contacto y el lugar de instalación.</x-ui.text>
                            </x-ui.stack>

                            @if ($errors->has('id_cliente'))
                                <x-ui.banner status="error" title="No se pudo identificar al cliente"
                                    :description="$errors->first('id_cliente')" />
                            @endif

                            <x-ui.stack direction="vertical" gap="3">
                                <x-ui.stack direction="vertical" gap="0.5">
                                    <x-ui.heading level="3">Servicio fijo</x-ui.heading>
                                    <x-ui.text type="supporting" color="secondary">Producto de catálogo, condiciones comerciales y región de atención.</x-ui.text>
                                </x-ui.stack>

                                <x-ui.selector name="id_producto" label="Producto" :options="$productOptions"
                                    :value="old('id_producto')" :error="$errors->first('id_producto')" required />

                                <x-ui.grid min-width="180" repeat="fit" gap="3">
                                    <x-ui.text-input name="cargo_fijo" label="Cargo fijo" type="number" min="0" step="0.01"
                                        :value="old('cargo_fijo')" description="Déjelo vacío para usar el precio del catálogo."
                                        :error="$errors->first('cargo_fijo')" optional />
                                    <x-ui.selector name="modalidad" label="Modalidad" :options="$modalidadOptions"
                                        :value="old('modalidad')" :error="$errors->first('modalidad')" required />
                                </x-ui.grid>

                                <x-ui.grid min-width="180" repeat="fit" gap="3">
                                    <x-ui.selector name="tecnologia" label="Tecnología" :options="$tecnologiaOptions"
                                        :value="old('tecnologia')" :error="$errors->first('tecnologia')" required />
                                    <x-ui.selector name="zonal" label="Región" :options="$regionOptions"
                                        :value="old('zonal')" :error="$errors->first('zonal')" required />
                                </x-ui.grid>
                            </x-ui.stack>

                            <x-ui.divider />

                            <x-ui.stack direction="vertical" gap="3">
                                <x-ui.stack direction="vertical" gap="0.5">
                                    <x-ui.heading level="3">Contacto</x-ui.heading>
                                    <x-ui.text type="supporting" color="secondary">Persona con quien se coordina la instalación.</x-ui.text>
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
                                    <x-ui.heading level="3">Instalación</x-ui.heading>
                                    <x-ui.text type="supporting" color="secondary">Ubicación acordada y contexto necesario para atender el caso.</x-ui.text>
                                </x-ui.stack>

                                <x-ui.text-input name="direccion" label="Dirección de instalación"
                                    :value="old('direccion')" :error="$errors->first('direccion')" required />
                                <x-ui.text-input name="coordenadas" label="Coordenadas" maxlength="100"
                                    :value="old('coordenadas')" :error="$errors->first('coordenadas')" optional />
                                <x-ui.text-area name="comentario" label="Comentario del ejecutivo"
                                    :value="old('comentario')" :error="$errors->first('comentario')" rows="4" optional />
                            </x-ui.stack>

                            <x-ui.stack direction="horizontal" gap="2" wrap="wrap">
                                <x-ui.button type="submit" variant="primary">Registrar nota de cargo</x-ui.button>
                                <x-ui.button :href="route('notas-cargo.fija.index')" variant="secondary">Cancelar</x-ui.button>
                            </x-ui.stack>
                        </x-ui.form-layout>
                    </form>
                </x-ui.card>
            </x-ui.grid>
        @endif
    </x-ui.table-page>
@endsection
