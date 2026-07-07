@extends('layouts.app')

@section('title', 'Liquidación')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('liquidaciones.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        {{-- Producto y validación: read-only context the liquidador settles against.
             The ejecutivo real (cartera propietario) and supervisor name shown in
             the legacy modal come from the datos/cartera module, not ported yet;
             reattach them here when it is. --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Nota de cargo móvil</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                @include('partials.campo', ['label' => 'Fecha ingreso', 'value' => $cargo->fecha_ingreso?->format('Y-m-d')])
                @include('partials.campo', ['label' => 'Actualización', 'value' => $cargo->fecha_actualizacion?->format('Y-m-d')])
                @include('partials.campo', ['label' => 'Estado', 'value' => $cargo->estado])
                @include('partials.campo', ['label' => 'RUC', 'value' => $cargo->ruc])
                @include('partials.campo', ['label' => 'Razón social', 'value' => $cargo->razon_social])
                @include('partials.campo', ['label' => 'Región', 'value' => $cargo->tienda?->nombre])
                @include('partials.campo', ['label' => 'Modalidad', 'value' => $cargo->modalidad])
                @include('partials.campo', ['label' => 'Q líneas', 'value' => $cargo->q_lineas])
                @include('partials.campo', ['label' => 'Cargo fijo', 'value' => $cargo->cargo_fijo])
                @include('partials.campo', ['label' => 'Ejecutivo real', 'value' => $cargo->ejecutivo?->personal])
                @include('partials.campo', ['label' => 'Oportunidad', 'value' => $cargo->oportunidad])
                @include('partials.campo', ['label' => 'Nodo', 'value' => $cargo->nodo])
                @include('partials.campo', ['label' => 'Comentario ejecutivo', 'value' => $cargo->comentario_ejecutivo])
            </dl>
            <div class="mt-4 border-t border-gray-100 pt-4">
                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                    @include('partials.campo', ['label' => 'Validación', 'value' => $cargo->validacion])
                    @include('partials.campo', ['label' => 'Fecha validación', 'value' => $cargo->fecha_validacion?->format('Y-m-d')])
                    @include('partials.campo', ['label' => 'Validador', 'value' => $cargo->validador?->personal])
                </dl>
                <div class="mt-4">
                    @include('partials.campo', ['label' => 'Comentario del validador', 'value' => $cargo->comentario_validador])
                </div>
            </div>
        </div>

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
