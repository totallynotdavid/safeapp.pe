@extends('layouts.app')

@section('title', 'Revisar quiebre')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('quiebres.validacion.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Detalle del quiebre</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                @include('quiebres.campo', ['label' => 'Fecha registro', 'value' => $quiebre->fecha_registro?->format('Y-m-d')])
                @include('quiebres.campo', ['label' => 'Inicio avería', 'value' => $quiebre->fecha_inicio_averia?->format('Y-m-d')])
                @include('quiebres.campo', ['label' => 'Activación', 'value' => $quiebre->fecha_activacion?->format('Y-m-d')])
                @include('quiebres.campo', ['label' => 'RUC', 'value' => $quiebre->ruc])
                @include('quiebres.campo', ['label' => 'Razón social', 'value' => $quiebre->razon_social])
                @include('quiebres.campo', ['label' => 'Servicio', 'value' => $quiebre->servicio])
                @include('quiebres.campo', ['label' => 'Tipo de avería', 'value' => $quiebre->tipo_averia])
                @include('quiebres.campo', ['label' => 'Problema', 'value' => $quiebre->problema_equipo])
                @include('quiebres.campo', ['label' => 'Back', 'value' => $quiebre->back])
                @include('quiebres.campo', ['label' => 'Ticket atención', 'value' => $quiebre->ticket_atencion])
                @include('quiebres.campo', ['label' => 'N° ticket', 'value' => $quiebre->numero_ticket])
                @include('quiebres.campo', ['label' => 'Fecha ticket', 'value' => $quiebre->fecha_ticket_atencion?->format('Y-m-d')])
                @include('quiebres.campo', ['label' => 'Ejecutivo', 'value' => $quiebre->ejecutivo?->personal])
                @include('quiebres.campo', ['label' => 'Q de líneas', 'value' => $quiebre->numero_problema])
                @include('quiebres.campo', ['label' => 'Región', 'value' => $quiebre->tienda?->nombre])
                @include('quiebres.campo', ['label' => 'Contacto 1', 'value' => $quiebre->contacto1])
                @include('quiebres.campo', ['label' => 'Celular 1', 'value' => $quiebre->celular1])
                @include('quiebres.campo', ['label' => 'Correo 1', 'value' => $quiebre->correo1])
                @include('quiebres.campo', ['label' => 'Contacto 2', 'value' => $quiebre->contacto2])
                @include('quiebres.campo', ['label' => 'Celular 2', 'value' => $quiebre->celular2])
                @include('quiebres.campo', ['label' => 'Correo 2', 'value' => $quiebre->correo2])
            </dl>
            <div class="mt-4 border-t border-gray-100 pt-4">
                @include('quiebres.campo', ['label' => 'Comentario del ejecutivo', 'value' => $quiebre->comentario_ejecutivo])
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Validación</h2>

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('quiebres.validacion.update', $quiebre) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                        <select id="estado" name="estado" required
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
                            @foreach ($estados as $opcion)
                                <option value="{{ $opcion }}" @selected(old('estado', $quiebre->estado) === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="casosf" class="block text-sm font-medium text-gray-700">Caso SF</label>
                        <input id="casosf" name="casosf" value="{{ old('casosf', $quiebre->casosf) }}" required
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="comentario_validador" class="block text-sm font-medium text-gray-700">Comentario del validador</label>
                    <textarea id="comentario_validador" name="comentario_validador" rows="3"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">{{ old('comentario_validador', $quiebre->comentario_validador) }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Guardar validación
                    </button>
                    <a href="{{ route('quiebres.validacion.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
