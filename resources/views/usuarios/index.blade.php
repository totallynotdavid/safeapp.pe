@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Usuarios</h2>
            <a href="{{ route('usuarios.create') }}" class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
                Nuevo usuario
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Usuario</th>
                        <th class="px-6 py-3">Nombre</th>
                        <th class="px-6 py-3">Rol</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($usuarios as $usuario)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-800">{{ $usuario->usuario }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $usuario->personal }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $usuario->rol?->nombre_rol ?? 'Sin rol' }}</td>
                            <td class="px-6 py-3">
                                @if ($usuario->estado)
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Habilitado</span>
                                @else
                                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-600">Inhabilitado</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('usuarios.edit', $usuario) }}" class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                        Editar
                                    </a>
                                    @unless ($usuario->id_usuario === auth()->id())
                                        <form method="POST" action="{{ route('usuarios.estado', $usuario) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-md px-3 py-1.5 text-xs font-medium {{ $usuario->estado ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                                {{ $usuario->estado ? 'Inhabilitar' : 'Habilitar' }}
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
