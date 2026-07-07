@extends('layouts.app')

@section('title', 'Datos personal')

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Datos personal</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Ejecutivo</th>
                        <th class="px-6 py-3">Rol</th>
                        <th class="px-6 py-3">Supervisor</th>
                        <th class="px-6 py-3">Nacimiento</th>
                        <th class="px-6 py-3">Teléfono</th>
                        <th class="px-6 py-3">Correo</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-800">{{ $usuario->personal }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $usuario->rol?->nombre_rol ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $usuario->supervisor?->nombre ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $usuario->fecha_nacimiento?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $usuario->telefono ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $usuario->correo ?? '—' }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('personal.edit', $usuario) }}" class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                No hay personal activo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
