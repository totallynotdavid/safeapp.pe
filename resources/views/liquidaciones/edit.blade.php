@extends('layouts.app')

@section('title', 'Liquidación')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('liquidaciones.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        @include('notas-cargo.movil.partials.detalle')

        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Registrar liquidación</h2>

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('liquidaciones.update', $cargo) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="comision" class="{{ $label }}">Comisión</label>
                        <input id="comision" name="comision" value="{{ old('comision', $cargo->comision) }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="observacion" class="{{ $label }}">Observación</label>
                        <select id="observacion" name="observacion" required class="{{ $field }}">
                            @foreach ($observaciones as $opcion)
                                <option value="{{ $opcion }}" @selected(old('observacion', $cargo->observacion) === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="descuento" class="{{ $label }}">Descuento</label>
                        <input id="descuento" name="descuento" value="{{ old('descuento', $cargo->descuento) }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="fecha_liquidacion" class="{{ $label }}">Fecha de liquidación</label>
                        <input type="date" id="fecha_liquidacion" name="fecha_liquidacion" value="{{ old('fecha_liquidacion', $cargo->fecha_liquidacion?->format('Y-m-d')) }}" required class="{{ $field }}">
                    </div>
                </div>

                <div>
                    <label for="comentario_liquidador" class="{{ $label }}">Comentario</label>
                    <textarea id="comentario_liquidador" name="comentario_liquidador" rows="4" class="{{ $field }}">{{ old('comentario_liquidador', $cargo->comentario_liquidador) }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Guardar liquidación
                    </button>
                    <a href="{{ route('liquidaciones.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
