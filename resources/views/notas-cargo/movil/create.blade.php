@extends('layouts.app')

@section('title', 'Nueva nota de cargo móvil')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('notas-cargo.movil.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        @include('partials.buscar-cliente', ['ruta' => 'notas-cargo.movil.create', 'ruc' => $ruc])

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

            <form method="POST" action="{{ route('notas-cargo.movil.store') }}" class="space-y-6">
                @csrf

                {{-- The client is resolved, not typed. Its name is never copied
                     onto the case. --}}
                <input type="hidden" name="id_cliente" value="{{ $cliente->id_cliente }}">

                {{-- El servicio --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="q_lineas" class="{{ $label }}">Q líneas</label>
                        <input type="number" min="1" id="q_lineas" name="q_lineas" value="{{ old('q_lineas') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="cargo_fijo" class="{{ $label }}">Cargo fijo</label>
                        <input type="number" step="0.01" min="0" id="cargo_fijo" name="cargo_fijo" value="{{ old('cargo_fijo') }}" required class="{{ $field }}">
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
                        <label for="tipo_producto" class="{{ $label }}">Tipo de producto</label>
                        <select id="tipo_producto" name="tipo_producto" required class="{{ $field }}">
                            @foreach ($tiposProducto as $opcion)
                                <option value="{{ $opcion }}" @selected(old('tipo_producto') === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="zonal" class="{{ $label }}">Región</label>
                        <select id="zonal" name="zonal" required class="{{ $field }}">
                            <option value="">-- Elegir --</option>
                            @foreach ($regiones as $region)
                                <option value="{{ $region->id_tienda }}" @selected((int) old('zonal') === $region->id_tienda)>{{ $region->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        {{-- Every región may choose from the whole catalog. Legacy
                             filtered this list by the región picked above, and all
                             22 executives sit in REGION LIMA, so a case filed for
                             anywhere else had an empty dropdown. --}}
                        <label for="id_cartera" class="{{ $label }}">Ejecutivo TDP</label>
                        <select id="id_cartera" name="id_cartera" class="{{ $field }}">
                            <option value="">-- Sin asignar --</option>
                            @foreach ($carteras as $cartera)
                                <option value="{{ $cartera->id_cartera }}" @selected((int) old('id_cartera') === $cartera->id_cartera)>{{ $cartera->propietario }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- El contacto --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="contacto" class="{{ $label }}">Nombre del contacto</label>
                        <input id="contacto" name="contacto" value="{{ old('contacto') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="telefono1" class="{{ $label }}">Teléfono 1</label>
                        <input id="telefono1" name="telefono1" value="{{ old('telefono1') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="telefono2" class="{{ $label }}">Teléfono 2 (opcional)</label>
                        <input id="telefono2" name="telefono2" value="{{ old('telefono2') }}" class="{{ $field }}">
                    </div>
                </div>

                {{-- La negociación --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="nivel_negociacion" class="{{ $label }}">Probabilidad de cierre</label>
                        <select id="nivel_negociacion" name="nivel_negociacion" required class="{{ $field }}">
                            @foreach ($niveles as $opcion)
                                <option value="{{ $opcion }}" @selected(old('nivel_negociacion') === $opcion)>{{ $opcion }} %</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="fecha_estimada" class="{{ $label }}">Fecha estimada de cierre</label>
                        <input type="date" id="fecha_estimada" name="fecha_estimada" value="{{ old('fecha_estimada') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="etapa" class="{{ $label }}">Fase</label>
                        <select id="etapa" name="etapa" required class="{{ $field }}">
                            @foreach ($etapas as $opcion)
                                <option value="{{ $opcion }}" @selected(old('etapa') === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="oportunidad" class="{{ $label }}">Oportunidad (opcional)</label>
                        <input id="oportunidad" name="oportunidad" value="{{ old('oportunidad') }}" class="{{ $field }}">
                    </div>
                </div>

                <div>
                    <label for="comentario_ejecutivo" class="{{ $label }}">Comentario</label>
                    <textarea id="comentario_ejecutivo" name="comentario_ejecutivo" rows="4" class="{{ $field }}">{{ old('comentario_ejecutivo') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Registrar
                    </button>
                    <a href="{{ route('notas-cargo.movil.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
                </div>
            </form>
        </div>
        @endif
    </div>
@endsection
