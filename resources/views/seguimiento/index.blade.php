@extends('layouts.app')

@section('title', 'Gestión diaria')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')

    <div class="space-y-6">
        <h1 class="text-xl font-semibold text-gray-800">Gestión del equipo</h1>

        {{-- Exportar is a second submit on this form, so the download always
             carries exactly the supervisor and range on screen. --}}
        <form method="GET" action="{{ route('seguimiento.index') }}" class="flex flex-wrap items-end gap-3 rounded-lg border border-gray-200 bg-white p-4">
            <div>
                <label for="supervisor" class="block text-sm font-medium text-gray-700">Supervisor</label>
                <select id="supervisor" name="supervisor" class="{{ $field }}">
                    <option value="">Elegir</option>
                    @foreach ($supervisores as $opcion)
                        <option value="{{ $opcion->id_supervisor }}" @selected($supervisor === $opcion->id_supervisor)>{{ $opcion->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="desde" class="block text-sm font-medium text-gray-700">Desde</label>
                <input id="desde" name="desde" type="date" value="{{ $rango->desde->toDateString() }}" class="{{ $field }}">
            </div>
            <div>
                <label for="hasta" class="block text-sm font-medium text-gray-700">Hasta</label>
                <input id="hasta" name="hasta" type="date" value="{{ $rango->hasta->toDateString() }}" class="{{ $field }}">
            </div>
            <button type="submit" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Filtrar</button>
            <button type="submit" formaction="{{ route('seguimiento.exportar') }}"
                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Exportar</button>
        </form>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Fecha</th>
                        <th class="px-6 py-3 text-left">Personal</th>
                        <th class="px-6 py-3 text-left">Cliente</th>
                        <th class="px-6 py-3 text-left">Contacto</th>
                        <th class="px-6 py-3 text-left">Respuesta</th>
                        <th class="px-6 py-3 text-left">Comentario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($contactos as $contacto)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-3 text-gray-600">{{ $contacto->fecha?->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $contacto->agente?->personal }}</td>
                            <td class="px-6 py-3">
                                <span class="font-medium text-gray-800">{{ $contacto->razon_social }}</span>
                                <span class="block text-xs text-gray-500">{{ $contacto->cliente?->ruc }}</span>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $contacto->gestion }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $contacto->respuesta?->descripcion }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $contacto->comentario }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                                {{ $supervisor === null ? 'Elija un supervisor.' : 'Sin gestiones en el rango elegido.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
