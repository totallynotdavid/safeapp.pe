@extends('layouts.app')

@section('title', 'Producción')
@section('page', 'true')

@section('content')
    @php
        $meses = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Setiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];
        $anoOptions = collect($filtro->anos())->map(fn ($ano) => ['value' => (string) $ano, 'label' => (string) $ano])->all();
        $mesOptions = collect($meses)->map(fn ($nombre, $valor) => ['value' => (string) $valor, 'label' => $nombre])->values()->all();
    @endphp

    <x-ui.table-page title="Producción por ejecutivo">
        <x-ui.text type="supporting" color="secondary">
            Compara los importes validados de fija y móvil por estado para el período elegido.
        </x-ui.text>

        <x-ui.stack
            id="produccion-filtros"
            as="form"
            method="GET"
            action="{{ route('produccion.index') }}"
            direction="horizontal"
            v-align="end"
            gap="3"
            wrap="wrap"
        >
            <x-ui.selector name="ano" label="Año" :options="$anoOptions" :value="(string) $filtro->ano" required />
            <x-ui.selector name="mes" label="Mes" :options="$mesOptions" :value="str_pad((string) $filtro->mes, 2, '0', STR_PAD_LEFT)" required />
            <x-ui.button type="submit" variant="primary">Filtrar</x-ui.button>
        </x-ui.stack>

        @foreach ($tablas as $tabla)
            <x-ui.stack direction="vertical" gap="2">
                <div class="stack" data-direction="horizontal" data-gap="2">
                    <div data-theme="stack-item" data-size="fill">
                        <x-ui.heading level="3">{{ $tabla['titulo'] }}</x-ui.heading>
                    </div>
                    <x-ui.badge variant="neutral">{{ $tabla['filas']->count() }} ejecutivos</x-ui.badge>
                </div>

                <x-ui.table label="Producción {{ strtolower($tabla['titulo']) }} por ejecutivo" :truncate="true">
                    <colgroup>
                        <col class="col-name">
                        @foreach ($tabla['estados'] as $estado)
                            <col class="col-count">
                        @endforeach
                        <col class="col-count">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col">Ejecutivo</th>
                            @foreach ($tabla['estados'] as $estado)
                                <th scope="col" class="table__numeric">{{ $estado }}</th>
                            @endforeach
                            <th scope="col" class="table__numeric">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tabla['filas'] as $fila)
                            <tr>
                                <td class="table__primary">{{ $fila['personal'] }}</td>
                                @foreach ($tabla['estados'] as $estado)
                                    <td class="table__numeric table__secondary">
                                        {{ number_format($fila['totales'][$estado] ?? 0, 2) }}
                                    </td>
                                @endforeach
                                <td class="table__numeric table__primary">
                                    {{ number_format($fila['totales']->sum(), 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($tabla['estados']) + 2 }}">
                                    <x-ui.empty-state
                                        title="No hay producción {{ strtolower($tabla['titulo']) }}"
                                        description="Prueba con otro mes o año."
                                        compact
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </x-ui.stack>
        @endforeach
    </x-ui.table-page>
@endsection
