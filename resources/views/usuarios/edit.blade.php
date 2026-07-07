@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
    <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6">
        <h2 class="mb-1 text-lg font-semibold text-gray-800">Editar usuario</h2>
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

        <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="personal" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input id="personal" name="personal" value="{{ old('personal', $usuario->personal) }}" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
            </div>

            <div>
                <label for="clave" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input id="clave" name="clave" type="password" autocomplete="new-password"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
                <p class="mt-1 text-xs text-gray-500">Dejar en blanco para conservar la contraseña actual.</p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Guardar
                </button>
                <a href="{{ route('usuarios.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
