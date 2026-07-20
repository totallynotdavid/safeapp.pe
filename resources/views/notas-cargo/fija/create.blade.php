@extends('layouts.app')

@section('title', 'Nueva nota de cargo fija')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('notas-cargo.fija.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        @include('partials.buscar-cliente', ['ruta' => 'notas-cargo.fija.create', 'ruc' => $ruc])

        @if ($ruc && ! $cliente)
            {{-- Legacy blocked here with "CLIENTE NO REGISTRADO: IR A FUNNEL
                 +AGREGAR CLIENTE", sending the agent to a different menu section
                 and back. The client still has to exist first; the trip is a
                 link now. --}}
            <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                El RUC {{ $ruc }} no está registrado.
                <a href="{{ route('clientes.create', ['ruc' => $ruc]) }}" class="font-medium underline">Regístrelo primero</a>
                y vuelva a buscarlo aquí.
            </div>
        @endif

        @if ($cliente)
        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-gray-800">{{ $cliente->razon_social }}</h2>
            <p class="mb-4 text-sm text-gray-500">RUC {{ $cliente->ruc }}</p>

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('notas-cargo.fija.store') }}" class="space-y-6">
                @csrf

                {{-- The client is resolved, not typed. Its name is never copied
                     onto the case. --}}
                <input type="hidden" name="id_cliente" value="{{ $cliente->id_cliente }}">

                {{-- El servicio. Legacy filled the descripción dropdown and then
                     the price with two more AJAX calls after the subtipo was
                     picked; the catalog is 65 rows, so it is one grouped select
                     with the price on the label. --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label for="id_producto" class="{{ $label }}">Producto</label>
                        <select id="id_producto" name="id_producto" required class="{{ $field }}">
                            <option value="">Elegir…</option>
                            @foreach ($catalogo as $subtipo => $productos)
                                <optgroup label="{{ $subtipo }}">
                                    @foreach ($productos as $producto)
                                        <option value="{{ $producto->id }}" @selected((int) old('id_producto') === $producto->id)>
                                            {{ $producto->descripcion }} — S/ {{ $producto->cargo_fijo }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="cargo_fijo" class="{{ $label }}">Cargo fijo</label>
                        <input type="number" step="0.01" min="0" id="cargo_fijo" name="cargo_fijo" value="{{ old('cargo_fijo') }}" class="{{ $field }}">
                        <p class="mt-1 text-xs text-gray-500">Vacío usa el precio del catálogo.</p>
                    </div>
                    <div>
                        <label for="modalidad" class="{{ $label }}">Modalidad</label>
                        <select id="modalidad" name="modalidad" required class="{{ $field }}">
                            @foreach ($modalidades as $opcion)
                                <option value="{{ $opcion }}" @selected(old('modalidad') === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="tecnologia" class="{{ $label }}">Tecnología</label>
                        <select id="tecnologia" name="tecnologia" required class="{{ $field }}">
                            @foreach ($tecnologias as $opcion)
                                <option value="{{ $opcion }}" @selected(old('tecnologia') === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="zonal" class="{{ $label }}">Región</label>
                        <select id="zonal" name="zonal" required class="{{ $field }}">
                            @foreach ($regiones as $region)
                                <option value="{{ $region->id_tienda }}" @selected((int) old('zonal') === $region->id_tienda)>{{ $region->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- El contacto --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="contacto" class="{{ $label }}">Contacto</label>
                        <input type="text" id="contacto" name="contacto" value="{{ old('contacto') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="telefono1" class="{{ $label }}">Teléfono 1</label>
                        <input type="text" id="telefono1" name="telefono1" value="{{ old('telefono1') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="telefono2" class="{{ $label }}">Teléfono 2</label>
                        <input type="text" id="telefono2" name="telefono2" value="{{ old('telefono2') }}" class="{{ $field }}">
                    </div>
                </div>

                {{-- La instalación --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="direccion" class="{{ $label }}">Dirección</label>
                        <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="coordenadas" class="{{ $label }}">Coordenadas</label>
                        <input type="text" id="coordenadas" name="coordenadas" value="{{ old('coordenadas') }}" class="{{ $field }}">
                    </div>
                </div>

                <div>
                    <label for="comentario" class="{{ $label }}">Comentario</label>
                    <textarea id="comentario" name="comentario" rows="4" class="{{ $field }}">{{ old('comentario') }}</textarea>
                </div>

                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Registrar nota de cargo
                </button>
            </form>
        </div>
        @endif
    </div>
@endsection
