@extends('layouts.app')

@section('title', 'Nota de cargo móvil')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('notas-cargo.movil.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        @include('notas-cargo.movil.partials.detalle')

        @if ($cargo->esEditable())
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="mb-4 text-lg font-semibold text-gray-800">Actualizar la nota de cargo</h2>

                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('notas-cargo.movil.update', $cargo) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="q_lineas" class="{{ $label }}">Q líneas</label>
                            <input type="number" min="1" id="q_lineas" name="q_lineas" value="{{ old('q_lineas', $cargo->q_lineas) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label for="cargo_fijo" class="{{ $label }}">Cargo fijo</label>
                            <input type="number" step="0.01" min="0" id="cargo_fijo" name="cargo_fijo" value="{{ old('cargo_fijo', $cargo->cargo_fijo) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label for="etapa" class="{{ $label }}">Fase</label>
                            <select id="etapa" name="etapa" required class="{{ $field }}">
                                @foreach ($etapas as $opcion)
                                    <option value="{{ $opcion }}" @selected(old('etapa', $cargo->etapa) === $opcion)>{{ $opcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="telefono1" class="{{ $label }}">Teléfono 1</label>
                            <input id="telefono1" name="telefono1" value="{{ old('telefono1', $cargo->telefono1) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label for="telefono2" class="{{ $label }}">Teléfono 2 (opcional)</label>
                            <input id="telefono2" name="telefono2" value="{{ old('telefono2', $cargo->telefono2) }}" class="{{ $field }}">
                        </div>
                        <div>
                            <label for="nivel_negociacion" class="{{ $label }}">Probabilidad de cierre</label>
                            <select id="nivel_negociacion" name="nivel_negociacion" required class="{{ $field }}">
                                @foreach ($niveles as $opcion)
                                    <option value="{{ $opcion }}" @selected(old('nivel_negociacion', $cargo->nivel_negociacion) === $opcion)>{{ $opcion }} %</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="fecha_estimada" class="{{ $label }}">Fecha estimada de cierre</label>
                            <input type="date" id="fecha_estimada" name="fecha_estimada" value="{{ old('fecha_estimada', $cargo->fecha_estimada?->format('Y-m-d')) }}" required class="{{ $field }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="oportunidad" class="{{ $label }}">Oportunidad (opcional)</label>
                            <input id="oportunidad" name="oportunidad" value="{{ old('oportunidad', $cargo->oportunidad) }}" class="{{ $field }}">
                        </div>
                    </div>

                    <div>
                        <label for="comentario_ejecutivo" class="{{ $label }}">Comentario</label>
                        <textarea id="comentario_ejecutivo" name="comentario_ejecutivo" rows="4" class="{{ $field }}">{{ old('comentario_ejecutivo', $cargo->comentario_ejecutivo) }}</textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Guardar
                        </button>
                        <a href="{{ route('notas-cargo.movil.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
                    </div>
                </form>
            </div>
        @else
            {{-- Once the validador has ruled, the figures they ruled on are
                 frozen. Legacy showed the form anyway and enforced this in
                 JavaScript. --}}
            <div class="rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                Esta nota de cargo ya fue validada y no admite cambios.
            </div>
        @endif
    </div>
@endsection
