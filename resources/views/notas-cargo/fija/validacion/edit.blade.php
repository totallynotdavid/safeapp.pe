@extends('layouts.app')

@section('title', 'Validar nota de cargo fija')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('notas-cargo.fija.validacion.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        @include('notas-cargo.fija.partials.detalle')

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

            {{-- Only the decision. Unlike móvil, nothing on a fija case is the
                 validador's to correct: a figure they disagree with is a
                 rejection, which is how the legacy fija write worked too. --}}
            <form method="POST" action="{{ route('notas-cargo.fija.validacion.update', $cargo) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="sm:w-1/3">
                    <label for="validacion" class="{{ $label }}">Decisión</label>
                    <select id="validacion" name="validacion" required class="{{ $field }}">
                        <option value="">-- Elegir --</option>
                        @foreach ($validaciones as $opcion)
                            <option value="{{ $opcion }}" @selected(old('validacion') === $opcion)>{{ $opcion }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="comentario_validador" class="{{ $label }}">Comentario</label>
                    <textarea id="comentario_validador" name="comentario_validador" rows="4" required class="{{ $field }}">{{ old('comentario_validador') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Guardar validación
                    </button>
                    <a href="{{ route('notas-cargo.fija.validacion.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
