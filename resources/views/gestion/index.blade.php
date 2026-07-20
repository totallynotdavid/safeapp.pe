@extends('layouts.app')

@section('title', 'Gestión ' . $tipo)

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 rounded-lg border border-gray-200 bg-white px-6 py-4">
        <h3 class="mb-3 font-semibold text-gray-800">Asignarme clientes</h3>

        <div class="flex flex-wrap items-end gap-3">
            <form method="POST" action="{{ route($rutaBase . '.asignar') }}"
                class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label for="origen" class="mb-1 block text-sm font-medium text-gray-700">Origen</label>
                    {{-- Options carry their stock count so the agent picks from
                         what exists. An origen with no stock is not offered. --}}
                    <select id="origen" name="origen" required
                        class="w-72 rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Elija un origen</option>
                        @foreach ($origenes as $disponible)
                            <option value="{{ $disponible->origen }}">
                                {{ $disponible->origen }} ({{ $disponible->disponibles }} disponibles)
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" @disabled($origenes->isEmpty())
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:bg-gray-300">
                    Asignarme hasta {{ $lote }}
                </button>
            </form>

            <form method="POST" action="{{ route($rutaBase . '.liberar') }}"
                onsubmit="return confirm('¿Devolver a la cola todos sus clientes {{ $tipo }} sin gestionar?')">
                @csrf
                <button type="submit"
                    class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                    Devolver mis pendientes
                </button>
            </form>
        </div>

        @if ($origenes->isEmpty())
            <p class="mt-3 text-sm text-gray-500">No hay clientes disponibles para asignar en este momento.</p>
        @endif
    </div>

    <div class="rounded-lg border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Gestión {{ $tipo }}</h2>
            <span class="text-sm text-gray-500">{{ $datos->total() }} por gestionar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-6 py-3">RUC</th>
                        <th class="px-6 py-3">Razón social</th>
                        <th class="px-6 py-3">Zonal</th>
                        <th class="px-6 py-3">Origen</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($datos as $dato)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-800">{{ $dato->cliente->ruc }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $dato->cliente->razon_social }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $dato->zonal ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $dato->origen ?? '—' }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('gestion.show', $dato) }}" class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                    Gestionar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                No tiene clientes pendientes en su tienda activa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($datos->hasPages())
            <div class="border-t border-gray-200 px-6 py-3">
                {{ $datos->links() }}
            </div>
        @endif
    </div>
@endsection
