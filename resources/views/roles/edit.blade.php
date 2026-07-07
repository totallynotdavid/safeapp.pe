@extends('layouts.app')

@section('title', 'Editar rol')

@section('content')
    <div class="max-w-3xl rounded-lg border border-gray-200 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">Editar rol</h2>

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('roles.update', $rol) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="nombre_rol" class="block text-sm font-medium text-gray-700">Nombre del rol</label>
                <input id="nombre_rol" name="nombre_rol" value="{{ old('nombre_rol', $rol->nombre_rol) }}" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
            </div>

            <div>
                <span class="block text-sm font-medium text-gray-700">Permisos</span>
                <p class="mb-2 text-xs text-gray-500">Cambiar esto no afecta a los usuarios existentes: sus accesos se copiaron al crearlos y pueden diferir a propósito.</p>
                @include('roles.permisos')
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Guardar
                </button>
                <a href="{{ route('roles.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
