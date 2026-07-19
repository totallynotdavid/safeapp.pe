@extends('layouts.app')

@section('title', 'Nuevo quiebre')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('quiebres.registro.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">&larr; Volver a la lista</a>

        @include('partials.buscar-cliente', ['ruta' => 'quiebres.registro.create', 'ruc' => $ruc])

        @if ($ruc && ! $cliente)
            {{-- The avería has to belong to a client record. Registering one is a
                 link away rather than a menu detour, which is what legacy's
                 "IR A FUNNEL + AGREGAR CLIENTE" dead-end made agents do. --}}
            <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                El RUC {{ $ruc }} no está registrado.
                <a href="{{ route('clientes.create', ['ruc' => $ruc]) }}" class="font-medium underline">Regístrelo primero</a>
                y vuelva a buscarlo aquí.
            </div>
        @endif

        @if ($cliente)
        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-gray-800">{{ $cliente->razon_social }}</h2>
            <p class="mb-4 text-sm text-gray-500">RUC {{ $cliente->ruc }}</p>

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('quiebres.registro.store') }}" class="space-y-6">
                @csrf

                {{-- The client is resolved, not typed. The name shown above comes
                     from the client record and is never copied onto the quiebre. --}}
                <input type="hidden" name="id_cliente" value="{{ $cliente->id_cliente }}">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="zonal_telefonica" class="{{ $label }}">Región</label>
                        <select id="zonal_telefonica" name="zonal_telefonica" required class="{{ $field }}">
                            <option value="">-- Elegir --</option>
                            @foreach ($tiendas as $tienda)
                                <option value="{{ $tienda->id_tienda }}" @selected((int) old('zonal_telefonica') === $tienda->id_tienda)>{{ $tienda->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="servicio" class="{{ $label }}">Servicio</label>
                        <select id="servicio" name="servicio" required class="{{ $field }}">
                            @foreach ($servicios as $opcion)
                                <option value="{{ $opcion }}" @selected(old('servicio') === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="tipo_averia" class="{{ $label }}">Tipo de avería</label>
                        <select id="tipo_averia" name="tipo_averia" required class="{{ $field }}">
                            @foreach ($tiposAveria as $valor => $texto)
                                <option value="{{ $valor }}" @selected(old('tipo_averia') === $valor)>{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Avería --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="fecha_activacion" class="{{ $label }}">Activación del servicio</label>
                        <input type="date" id="fecha_activacion" name="fecha_activacion" value="{{ old('fecha_activacion') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="fecha_inicio_averia" class="{{ $label }}">Inicio de avería</label>
                        <input type="date" id="fecha_inicio_averia" name="fecha_inicio_averia" value="{{ old('fecha_inicio_averia') }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="numero_problema" class="{{ $label }}">Q líneas con avería</label>
                        <input type="number" min="1" id="numero_problema" name="numero_problema" value="{{ old('numero_problema') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="problema_equipo" class="{{ $label }}">Problema con el equipo</label>
                        <select id="problema_equipo" name="problema_equipo" required class="{{ $field }}">
                            @foreach (['NO', 'SI'] as $opcion)
                                <option value="{{ $opcion }}" @selected(old('problema_equipo') === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="detalle_equipo" class="{{ $label }}">Detalle del equipo</label>
                        <select id="detalle_equipo" name="detalle_equipo" class="{{ $field }}">
                            <option value="">-- Ninguno --</option>
                            @foreach ($detalles as $opcion)
                                <option value="{{ $opcion }}" @selected(old('detalle_equipo') === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="back" class="{{ $label }}">Back</label>
                        <input id="back" name="back" value="{{ old('back') }}" class="{{ $field }}">
                    </div>
                </div>

                {{-- Ticket. Required by the server once the service has been active for
                     90 días o más; otherwise leave blank. --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="numero_ticket" class="{{ $label }}">Número de ticket</label>
                        <input id="numero_ticket" name="numero_ticket" value="{{ old('numero_ticket') }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="fecha_ticket_atencion" class="{{ $label }}">Fecha de ticket</label>
                        <input type="date" id="fecha_ticket_atencion" name="fecha_ticket_atencion" value="{{ old('fecha_ticket_atencion') }}" class="{{ $field }}">
                    </div>
                </div>

                {{-- Contactos --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="contacto1" class="{{ $label }}">Contacto 1</label>
                        <input id="contacto1" name="contacto1" value="{{ old('contacto1') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="celular1" class="{{ $label }}">Celular 1</label>
                        <input id="celular1" name="celular1" value="{{ old('celular1') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="correo1" class="{{ $label }}">Correo 1</label>
                        <input type="email" id="correo1" name="correo1" value="{{ old('correo1') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        <label for="contacto2" class="{{ $label }}">Contacto 2 (opcional)</label>
                        <input id="contacto2" name="contacto2" value="{{ old('contacto2') }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="celular2" class="{{ $label }}">Celular 2 (opcional)</label>
                        <input id="celular2" name="celular2" value="{{ old('celular2') }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="correo2" class="{{ $label }}">Correo 2 (opcional)</label>
                        <input type="email" id="correo2" name="correo2" value="{{ old('correo2') }}" class="{{ $field }}">
                    </div>
                </div>

                <div>
                    <label for="comentario_ejecutivo" class="{{ $label }}">Comentario</label>
                    <textarea id="comentario_ejecutivo" name="comentario_ejecutivo" rows="4" class="{{ $field }}">{{ old('comentario_ejecutivo') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Registrar
                    </button>
                    <a href="{{ route('quiebres.registro.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
                </div>
            </form>
        </div>
        @endif
    </div>
@endsection
