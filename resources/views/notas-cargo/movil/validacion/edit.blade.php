@extends('layouts.app')

@section('title', 'Validar nota de cargo móvil')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('notas-cargo.movil.validacion.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        @include('notas-cargo.movil.partials.detalle')

        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Registrar la validación</h2>

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('notas-cargo.movil.validacion.update', $cargo) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="validacion" class="{{ $label }}">Decisión</label>
                        <select id="validacion" name="validacion" required class="{{ $field }}">
                            <option value="">-- Elegir --</option>
                            @foreach ($validaciones as $opcion)
                                <option value="{{ $opcion }}" @selected(old('validacion') === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- The two figures the validador may correct before ruling.
                         Nothing else on the case is theirs to change. --}}
                    <div>
                        <label for="q_lineas" class="{{ $label }}">Q líneas</label>
                        <input type="number" min="1" id="q_lineas" name="q_lineas" value="{{ old('q_lineas', $cargo->q_lineas) }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="cargo_fijo" class="{{ $label }}">Cargo fijo</label>
                        <input type="number" step="0.01" min="0" id="cargo_fijo" name="cargo_fijo" value="{{ old('cargo_fijo', $cargo->cargo_fijo) }}" required class="{{ $field }}">
                    </div>
                </div>

                <div>
                    <label for="comentario_validador" class="{{ $label }}">Comentario</label>
                    <textarea id="comentario_validador" name="comentario_validador" rows="4" required class="{{ $field }}">{{ old('comentario_validador') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Guardar validación
                    </button>
                    <a href="{{ route('notas-cargo.movil.validacion.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
