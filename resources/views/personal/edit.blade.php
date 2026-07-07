@extends('layouts.app')

@section('title', 'Editar datos personal')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6">
        <h2 class="mb-1 text-lg font-semibold text-gray-800">Editar datos personal</h2>
        <p class="mb-4 text-sm text-gray-500">{{ $usuario->usuario }} · {{ $usuario->rol?->nombre_rol ?? 'Sin rol' }}</p>

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('personal.update', $usuario) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="personal" class="{{ $label }}">Nombre completo</label>
                <input id="personal" name="personal" value="{{ old('personal', $usuario->personal) }}" required class="{{ $field }}">
            </div>

            <div>
                <label for="fecha_nacimiento" class="{{ $label }}">Fecha de nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $usuario->fecha_nacimiento?->format('Y-m-d')) }}" class="{{ $field }}">
            </div>

            <div>
                <label for="telefono" class="{{ $label }}">Teléfono</label>
                <input id="telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" class="{{ $field }}">
            </div>

            <div>
                <label for="correo" class="{{ $label }}">Correo</label>
                <input type="email" id="correo" name="correo" value="{{ old('correo', $usuario->correo) }}" class="{{ $field }}">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Guardar
                </button>
                <a href="{{ route('personal.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
