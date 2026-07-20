@extends('layouts.app')

@section('title', 'Nota de cargo fija')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('notas-cargo.fija.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        @include('notas-cargo.fija.partials.detalle')

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

                {{-- The product, the price and the región are not here: they are
                     what the case is, and changing them would make it a
                     different case. What an ejecutivo genuinely corrects before
                     validación is how to reach the client and where to install.
                     --}}
                <form method="POST" action="{{ route('notas-cargo.fija.update', $cargo) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="contacto" class="{{ $label }}">Contacto</label>
                            <input id="contacto" name="contacto" value="{{ old('contacto', $cargo->contacto) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label for="telefono1" class="{{ $label }}">Teléfono 1</label>
                            <input id="telefono1" name="telefono1" value="{{ old('telefono1', $cargo->telefono1) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label for="telefono2" class="{{ $label }}">Teléfono 2 (opcional)</label>
                            <input id="telefono2" name="telefono2" value="{{ old('telefono2', $cargo->telefono2) }}" class="{{ $field }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="direccion" class="{{ $label }}">Dirección</label>
                            <input id="direccion" name="direccion" value="{{ old('direccion', $cargo->direccion) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label for="coordenadas" class="{{ $label }}">Coordenadas (opcional)</label>
                            <input id="coordenadas" name="coordenadas" value="{{ old('coordenadas', $cargo->coordenadas) }}" class="{{ $field }}">
                        </div>
                        <div>
                            <label for="tecnologia" class="{{ $label }}">Tecnología</label>
                            <select id="tecnologia" name="tecnologia" required class="{{ $field }}">
                                @foreach ($tecnologias as $opcion)
                                    <option value="{{ $opcion }}" @selected(old('tecnologia', $cargo->tecnologia) === $opcion)>{{ $opcion }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="comentario" class="{{ $label }}">Comentario</label>
                        <textarea id="comentario" name="comentario" rows="4" class="{{ $field }}">{{ old('comentario', $cargo->comentario) }}</textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Guardar
                        </button>
                        <a href="{{ route('notas-cargo.fija.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
                    </div>
                </form>
            </div>
        @else
            {{-- Once the validador has ruled, the case is frozen. Legacy showed
                 the form anyway and enforced this in JavaScript. --}}
            <div class="rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                Esta nota de cargo ya fue validada y no admite cambios.
            </div>
        @endif
    </div>
@endsection
