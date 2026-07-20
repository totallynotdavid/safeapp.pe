@extends('layouts.app')

@section('title', 'Quiebre')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('quiebres.registro.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Detalle del quiebre</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                @include('partials.campo', ['label' => 'Fecha registro', 'value' => $quiebre->fecha_registro?->format('Y-m-d')])
                @include('partials.campo', ['label' => 'Activación', 'value' => $quiebre->fecha_activacion?->format('Y-m-d')])
                @include('partials.campo', ['label' => 'Inicio avería', 'value' => $quiebre->fecha_inicio_averia?->format('Y-m-d')])
                @include('partials.campo', ['label' => 'RUC', 'value' => $quiebre->cliente->ruc])
                @include('partials.campo', ['label' => 'Razón social', 'value' => $quiebre->cliente->razon_social])
                @include('partials.campo', ['label' => 'Región', 'value' => $quiebre->tienda?->nombre])
                @include('partials.campo', ['label' => 'Servicio', 'value' => $quiebre->servicio])
                @include('partials.campo', ['label' => 'Tipo de avería', 'value' => $tiposAveria[$quiebre->tipo_averia] ?? $quiebre->tipo_averia])
                @include('partials.campo', ['label' => 'Estado', 'value' => $quiebre->estado])
                @include('partials.campo', ['label' => 'Problema equipo', 'value' => $quiebre->problema_equipo])
                @include('partials.campo', ['label' => 'Detalle equipo', 'value' => $quiebre->detalle_equipo])
                @include('partials.campo', ['label' => 'Back', 'value' => $quiebre->back])
                @include('partials.campo', ['label' => 'Ticket atención', 'value' => $quiebre->ticket_atencion])
                @include('partials.campo', ['label' => 'N° ticket', 'value' => $quiebre->numero_ticket])
                @include('partials.campo', ['label' => 'Fecha ticket', 'value' => $quiebre->fecha_ticket_atencion?->format('Y-m-d')])
            </dl>
        </div>

        @if ($quiebre->esEditable())
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="mb-4 text-lg font-semibold text-gray-800">Actualizar seguimiento</h2>

                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('quiebres.registro.update', $quiebre) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="numero_problema" class="{{ $label }}">Q líneas con avería</label>
                            <input type="number" min="1" id="numero_problema" name="numero_problema" value="{{ old('numero_problema', $quiebre->numero_problema) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label for="contacto1" class="{{ $label }}">Contacto 1</label>
                            <input id="contacto1" name="contacto1" value="{{ old('contacto1', $quiebre->contacto1) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label for="celular1" class="{{ $label }}">Celular 1</label>
                            <input id="celular1" name="celular1" value="{{ old('celular1', $quiebre->celular1) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label for="correo1" class="{{ $label }}">Correo 1</label>
                            <input type="email" id="correo1" name="correo1" value="{{ old('correo1', $quiebre->correo1) }}" required class="{{ $field }}">
                        </div>
                        <div>
                            <label for="contacto2" class="{{ $label }}">Contacto 2</label>
                            <input id="contacto2" name="contacto2" value="{{ old('contacto2', $quiebre->contacto2) }}" class="{{ $field }}">
                        </div>
                        <div>
                            <label for="celular2" class="{{ $label }}">Celular 2</label>
                            <input id="celular2" name="celular2" value="{{ old('celular2', $quiebre->celular2) }}" class="{{ $field }}">
                        </div>
                        <div>
                            <label for="correo2" class="{{ $label }}">Correo 2</label>
                            <input type="email" id="correo2" name="correo2" value="{{ old('correo2', $quiebre->correo2) }}" class="{{ $field }}">
                        </div>
                    </div>

                    <div>
                        <label for="comentario_ejecutivo" class="{{ $label }}">Comentario</label>
                        <textarea id="comentario_ejecutivo" name="comentario_ejecutivo" rows="4" class="{{ $field }}">{{ old('comentario_ejecutivo', $quiebre->comentario_ejecutivo) }}</textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Guardar cambios
                        </button>
                        <a href="{{ route('quiebres.registro.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
                    </div>
                </form>
            </div>
        @else
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-6 py-4 text-sm text-amber-800">
                Este quiebre está en estado <strong>{{ $quiebre->estado }}</strong> y ya no puede editarse.
            </div>
        @endif

        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Validación</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                @include('partials.campo', ['label' => 'Validador', 'value' => $quiebre->validador?->personal])
                @include('partials.campo', ['label' => 'Fecha validación', 'value' => $quiebre->fecha_validacion?->format('Y-m-d')])
                @include('partials.campo', ['label' => 'Caso SF', 'value' => $quiebre->caso_sf])
            </dl>
            <div class="mt-4 border-t border-gray-100 pt-4">
                @include('partials.campo', ['label' => 'Comentario del validador', 'value' => $quiebre->comentario_validador])
            </div>
        </div>
    </div>
@endsection
