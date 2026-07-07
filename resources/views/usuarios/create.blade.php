@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')
    <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">Nuevo usuario</h2>

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="usuario" class="block text-sm font-medium text-gray-700">Usuario</label>
                <input id="usuario" name="usuario" value="{{ old('usuario') }}" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
            </div>

            <div>
                <label for="personal" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input id="personal" name="personal" value="{{ old('personal') }}" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
            </div>

            <div>
                <label for="clave" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input id="clave" name="clave" type="password" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
            </div>

            <div>
                <label for="id_rol" class="block text-sm font-medium text-gray-700">Rol</label>
                <select id="id_rol" name="id_rol" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
                    <option value="">-- Elegir --</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id_rol }}" @selected(old('id_rol') == $rol->id_rol)>{{ $rol->nombre_rol }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="sexo" class="block text-sm font-medium text-gray-700">Sexo</label>
                <select id="sexo" name="sexo" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
                    <option value="">-- Elegir --</option>
                    <option value="M" @selected(old('sexo') === 'M')>Masculino</option>
                    <option value="F" @selected(old('sexo') === 'F')>Femenino</option>
                </select>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Crear
                </button>
                <a href="{{ route('usuarios.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
