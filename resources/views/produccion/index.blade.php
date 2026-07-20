@extends('layouts.app')

@section('title', 'Producción')

@section('content')
    @php($control = 'mt-1 rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:border-gray-500 focus:outline-none')
    @php($meses = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Setiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'])

    <div class="space-y-6">
        <h1 class="text-xl font-semibold text-gray-800">Producción por ejecutivo</h1>

        <form method="GET" action="{{ route('produccion.index') }}" class="flex flex-wrap items-end gap-3 rounded-lg border border-gray-200 bg-white p-4">
            <div>
                <label for="ano" class="block text-xs font-medium text-gray-500">Año</label>
                <select id="ano" name="ano" class="{{ $control }}">
                    @foreach ($filtro->anos() as $opcion)
                        <option value="{{ $opcion }}" @selected($filtro->ano === $opcion)>{{ $opcion }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="mes" class="block text-xs font-medium text-gray-500">Mes</label>
                <select id="mes" name="mes" class="{{ $control }}">
                    @foreach ($meses as $valor => $nombre)
                        <option value="{{ $valor }}" @selected($filtro->mes === (int) $valor)>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">Filtrar</button>
        </form>

        @foreach ($tablas as $tabla)
            <div class="space-y-2">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">{{ $tabla['titulo'] }}</h2>
                <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Ejecutivo</th>
                                @foreach ($tabla['estados'] as $estado)
                                    <th class="px-4 py-3 text-right">{{ $estado }}</th>
                                @endforeach
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($tabla['filas'] as $fila)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-800">{{ $fila['personal'] }}</td>
                                    @foreach ($tabla['estados'] as $estado)
                                        <td class="px-4 py-2 text-right text-gray-600">
                                            {{ number_format($fila['totales'][$estado] ?? 0, 2) }}
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-2 text-right font-semibold text-gray-800">
                                        {{ number_format($fila['totales']->sum(), 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($tabla['estados']) + 2 }}" class="px-4 py-6 text-center text-gray-500">
                                        Sin producción en el período elegido.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection
