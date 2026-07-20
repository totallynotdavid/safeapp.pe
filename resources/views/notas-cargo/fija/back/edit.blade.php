@extends('layouts.app')

@section('title', 'Actualizar nota de cargo fija')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('notas-cargo.fija.back.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        @include('notas-cargo.fija.partials.detalle')

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

            <form method="POST" action="{{ route('notas-cargo.fija.back.update', $cargo) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="estado" class="{{ $label }}">Estado</label>
                        <select id="estado" name="estado" required class="{{ $field }}">
                            @foreach ($estados as $opcion)
                                <option value="{{ $opcion }}" @selected(old('estado', $cargo->estado) === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- The month a settled case counts in. Required once the
                         estado is COMPLETADO, because every queue windows a
                         completed case by it and one without it is invisible to
                         the screen that closed it. --}}
                    <div>
                        <label for="fecha_liquidacion" class="{{ $label }}">Fecha de liquidación</label>
                        <input type="date" id="fecha_liquidacion" name="fecha_liquidacion" value="{{ old('fecha_liquidacion', $cargo->fecha_liquidacion?->format('Y-m-d')) }}" class="{{ $field }}">
                    </div>
                    {{-- Back may correct the región the ejecutivo chose. Legacy
                         wrote the correction to a second column and left this
                         one standing, so the two disagreed on 1,835 cases. --}}
                    <div>
                        <label for="zonal" class="{{ $label }}">Región</label>
                        <select id="zonal" name="zonal" required class="{{ $field }}">
                            @foreach ($regiones as $region)
                                <option value="{{ $region->id_tienda }}" @selected((int) old('zonal', $cargo->zonal) === $region->id_tienda)>{{ $region->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="nodo" class="{{ $label }}">Nodo</label>
                        <select id="nodo" name="nodo" required class="{{ $field }}">
                            @foreach ($nodos as $opcion)
                                <option value="{{ $opcion }}" @selected(old('nodo', $cargo->nodo) === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="peticion" class="{{ $label }}">Petición</label>
                        <input id="peticion" name="peticion" value="{{ old('peticion', $cargo->peticion) }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="contrata" class="{{ $label }}">Contrata</label>
                        <input id="contrata" name="contrata" value="{{ old('contrata', $cargo->contrata) }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="nasignado" class="{{ $label }}">N° asignado</label>
                        <input id="nasignado" name="nasignado" value="{{ old('nasignado', $cargo->nasignado) }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="caso_sf" class="{{ $label }}">Caso SF</label>
                        <input id="caso_sf" name="caso_sf" value="{{ old('caso_sf', $cargo->caso_sf) }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="ncfinanciera" class="{{ $label }}">Cuenta financiera</label>
                        <input id="ncfinanciera" name="ncfinanciera" value="{{ old('ncfinanciera', $cargo->ncfinanciera) }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="ncfe" class="{{ $label }}">NC facturación</label>
                        <input id="ncfe" name="ncfe" value="{{ old('ncfe', $cargo->ncfe) }}" class="{{ $field }}">
                    </div>
                </div>

                <div>
                    <label for="comentario_back" class="{{ $label }}">Comentario</label>
                    <textarea id="comentario_back" name="comentario_back" rows="4" required class="{{ $field }}">{{ old('comentario_back', $cargo->comentario_back) }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Guardar
                    </button>
                    <a href="{{ route('notas-cargo.fija.back.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
