@extends('layouts.app')

@section('title', 'Liquidación')
@section('page', 'true')

@section('content')
    @php($grupos = collect($estados)->mapWithKeys(fn ($estado) => [$estado => $cargos->where('estado', $estado)])->filter->isNotEmpty())

    <x-ui.table-page title="Notas de cargo por liquidar">
        <x-ui.text type="supporting" color="secondary">Revise y complete la liquidación de las notas de cargo pendientes.</x-ui.text>

        <x-ui.card padding="4">
            @include('partials.filtros-ano-estado', [
                'ruta' => 'liquidaciones.index',
                'formId' => 'liquidaciones-filtros',
            ])
        </x-ui.card>

        <x-ui.table label="Notas de cargo por liquidar" :truncate="true">
                                <thead><tr>
                                    <th scope="col">Actualización</th>
                                    <th scope="col">RUC</th>
                                    <th scope="col">Razón social</th>
                                    <th scope="col">Modalidad</th>
                                    <th scope="col" class="table__numeric">Líneas</th>
                                    <th scope="col">Ejecutivo</th>
                                    <th scope="col" class="table__actions">Acciones</th>
                                </tr></thead>
                                @if ($grupos->isEmpty())
                                    <tbody><tr><td colspan="7">
                                        <x-ui.empty-state title="No hay notas por liquidar" description="No encontramos notas de cargo para los filtros seleccionados." compact />
                                    </td></tr></tbody>
                                @else
                                    @foreach ($grupos as $estado => $grupo)
                                        <x-ui.grouped-table-section id="liquidaciones-{{ $estado }}" :label="\App\Support\EtiquetasDeDominio::texto($estado, 'estado')" :count="$grupo->count()" :columns="7">
                                        @foreach ($grupo as $cargo)
                                        <tr>
                                            <td class="table__secondary">{{ $cargo->fecha_actualizacion?->format('d/m/Y') }}</td>
                                            <td class="table__primary">{{ $cargo->cliente?->ruc }}</td>
                                            <td>{{ $cargo->cliente?->razon_social }}</td>
                                            <td class="table__secondary">{{ \App\Support\EtiquetasDeDominio::texto($cargo->modalidad, 'modalidad') }}</td>
                                            <td class="table__numeric">{{ $cargo->q_lineas }}</td>
                                            <td class="table__secondary">{{ $cargo->ejecutivo?->personal }}</td>
                                            <td class="table__actions">
                                                <x-ui.button href="{{ route('liquidaciones.edit', $cargo) }}" variant="secondary" size="sm">Liquidar</x-ui.button>
                                            </td>
                                        </tr>
                                        @endforeach
                                        </x-ui.grouped-table-section>
                                    @endforeach
                                @endif
        </x-ui.table>
    </x-ui.table-page>
@endsection
