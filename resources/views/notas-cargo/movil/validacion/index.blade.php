@extends('layouts.app')

@section('title', 'Validación móvil')

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white">
        <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Notas de cargo móvil por validar</h2>

            {{-- Año only. A case waiting on a decision has not reached the back
                 office, so its estado is still the PENDIENTE it was filed with
                 and an estado filter would have one option. --}}
            <form method="GET" action="{{ route('notas-cargo.movil.validacion.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="ano" class="block text-xs font-medium text-gray-500">Año</label>
                    <select id="ano" name="ano" class="mt-1 rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:border-gray-500 focus:outline-none">
                        @foreach ($anos as $opcion)
                            <option value="{{ $opcion }}" @selected($ano === $opcion)>{{ $opcion }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
                    Filtrar
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Ingreso</th>
                        <th class="px-6 py-3">RUC</th>
                        <th class="px-6 py-3">Razón social</th>
                        <th class="px-6 py-3">Modalidad</th>
                        <th class="px-6 py-3 text-right">Q líneas</th>
                        <th class="px-6 py-3 text-right">Cargo fijo</th>
                        <th class="px-6 py-3">Ejecutivo</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($cargos as $cargo)
                        <tr>
                            <td class="px-6 py-3 text-gray-600">{{ $cargo->fecha_ingreso?->format('Y-m-d') }}</td>
                            <td class="px-6 py-3 font-medium text-gray-800">{{ $cargo->cliente?->ruc }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $cargo->cliente?->razon_social }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $cargo->modalidad }}</td>
                            <td class="px-6 py-3 text-right text-gray-600">{{ $cargo->q_lineas }}</td>
                            <td class="px-6 py-3 text-right text-gray-600">{{ $cargo->cargo_fijo }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $cargo->ejecutivo?->personal }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('notas-cargo.movil.validacion.edit', $cargo) }}" class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                    Validar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">
                                No hay notas de cargo pendientes de validación.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
