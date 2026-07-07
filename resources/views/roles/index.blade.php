@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Roles</h2>
            <a href="{{ route('roles.create') }}" class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
                Nuevo rol
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Rol</th>
                        <th class="px-6 py-3">Usuarios</th>
                        <th class="px-6 py-3">Permisos</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($roles as $rol)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-800">{{ $rol->nombre_rol }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $rol->usuarios_count }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $rol->menus_count }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('roles.edit', $rol) }}" class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('roles.destroy', $rol) }}" onsubmit="return confirm('¿Eliminar este rol?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" @disabled($rol->usuarios_count > 0)
                                            class="rounded-md px-3 py-1.5 text-xs font-medium {{ $rol->usuarios_count > 0 ? 'cursor-not-allowed bg-gray-100 text-gray-400' : 'bg-red-50 text-red-700 hover:bg-red-100' }}"
                                            @if ($rol->usuarios_count > 0) title="Tiene usuarios asignados" @endif>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
