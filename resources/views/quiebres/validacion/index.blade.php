@extends('layouts.app')

@section('title', 'Validación de quiebres')

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($sinAsignacion)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-6 py-4 text-sm text-amber-800">
            No tienes una tienda asignada, así que no hay quiebres que validar. Pide a un supervisor que te asigne una.
        </div>
    @else
        <div class="rounded-lg border border-gray-200 bg-white">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-800">Quiebres por validar</h2>

                <form method="GET" action="{{ route('quiebres.validacion.index') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="ano" class="block text-xs font-medium text-gray-500">Año</label>
                        <select id="ano" name="ano" class="mt-1 rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:border-gray-500 focus:outline-none">
                            @foreach ($anos as $opcion)
                                <option value="{{ $opcion }}" @selected($ano === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="estado" class="block text-xs font-medium text-gray-500">Estado</label>
                        <select id="estado" name="estado" class="mt-1 rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:border-gray-500 focus:outline-none">
                            <option value="">Todos</option>
                            @foreach ($estados as $opcion)
                                <option value="{{ $opcion }}" @selected($estado === $opcion)>{{ $opcion }}</option>
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
                            <th class="px-6 py-3">Fecha registro</th>
                            <th class="px-6 py-3">RUC</th>
                            <th class="px-6 py-3">Razón social</th>
                            <th class="px-6 py-3">Servicio</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Back</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($quiebres as $quiebre)
                            <tr>
                                <td class="px-6 py-3 text-gray-600">{{ $quiebre->fecha_registro?->format('Y-m-d') }}</td>
                                <td class="px-6 py-3 font-medium text-gray-800">{{ $quiebre->cliente->ruc }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $quiebre->cliente->razon_social }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $quiebre->servicio }}</td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $quiebre->estado }}</span>
                                </td>
                                <td class="px-6 py-3 text-gray-600">{{ $quiebre->back }}</td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('quiebres.validacion.edit', $quiebre) }}" class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                        Revisar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No hay quiebres para los filtros elegidos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
