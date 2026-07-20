@extends('layouts.app')

@section('title', 'Seguimiento de nota de cargo móvil')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('notas-cargo.movil.back.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        @include('notas-cargo.movil.partials.detalle')

        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Actualizar el seguimiento</h2>

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('notas-cargo.movil.back.update', $cargo) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    {{-- estado is this stage's column: no other screen writes it. --}}
                    <div>
                        <label for="estado" class="{{ $label }}">Estado</label>
                        <select id="estado" name="estado" required class="{{ $field }}">
                            @foreach ($estados as $opcion)
                                <option value="{{ $opcion }}" @selected(old('estado', $cargo->estado) === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="fecha_actualizacion" class="{{ $label }}">Fecha del estado</label>
                        <input type="date" id="fecha_actualizacion" name="fecha_actualizacion" value="{{ old('fecha_actualizacion', $cargo->fecha_actualizacion?->format('Y-m-d')) }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="etapa" class="{{ $label }}">Fase</label>
                        <select id="etapa" name="etapa" required class="{{ $field }}">
                            @foreach ($etapas as $opcion)
                                <option value="{{ $opcion }}" @selected(old('etapa', $cargo->etapa) === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Back may correct the región the ejecutivo chose. Legacy
                         wrote the correction to a second column instead, so the
                         two disagreed on 46 cases. --}}
                    <div>
                        <label for="zonal" class="{{ $label }}">Región</label>
                        <select id="zonal" name="zonal" required class="{{ $field }}">
                            @foreach ($regiones as $region)
                                <option value="{{ $region->id_tienda }}" @selected((int) old('zonal', $cargo->zonal) === $region->id_tienda)>{{ $region->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="id_cartera" class="{{ $label }}">Ejecutivo TDP</label>
                        <select id="id_cartera" name="id_cartera" class="{{ $field }}">
                            <option value="">-- Sin asignar --</option>
                            @foreach ($carteras as $cartera)
                                <option value="{{ $cartera->id_cartera }}" @selected((int) old('id_cartera', $cargo->id_cartera) === $cartera->id_cartera)>{{ $cartera->propietario }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="nodo" class="{{ $label }}">Nodo</label>
                        <input id="nodo" name="nodo" value="{{ old('nodo', $cargo->nodo) }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="oportunidad" class="{{ $label }}">Oportunidad (opcional)</label>
                        <input id="oportunidad" name="oportunidad" value="{{ old('oportunidad', $cargo->oportunidad) }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="casofwa" class="{{ $label }}">Caso FWA (opcional)</label>
                        <input id="casofwa" name="casofwa" value="{{ old('casofwa', $cargo->casofwa) }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="ciclo" class="{{ $label }}">Ciclo (opcional)</label>
                        <input id="ciclo" name="ciclo" value="{{ old('ciclo', $cargo->ciclo) }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="cuenta_financiera" class="{{ $label }}">Cuenta financiera (opcional)</label>
                        <input id="cuenta_financiera" name="cuenta_financiera" value="{{ old('cuenta_financiera', $cargo->cuenta_financiera) }}" class="{{ $field }}">
                    </div>
                </div>

                <div>
                    <label for="comentario_back" class="{{ $label }}">Comentario</label>
                    <textarea id="comentario_back" name="comentario_back" rows="4" required class="{{ $field }}">{{ old('comentario_back', $cargo->comentario_back) }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Guardar seguimiento
                    </button>
                    <a href="{{ route('notas-cargo.movil.back.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
