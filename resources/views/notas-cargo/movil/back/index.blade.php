@extends('layouts.app')

@section('title', 'Back móvil')
@section('page', true)

@section('content')
    @php
        $grupos = collect($estados)->mapWithKeys(fn ($estado) => [$estado => $cargos->where('estado', $estado)])->filter->isNotEmpty();
    @endphp
    <x-ui.table-page title="Notas de cargo móvil en seguimiento">
        <x-ui.text type="supporting" color="secondary">
            Actualice y exporte las oportunidades aprobadas que avanzan por back office.
        </x-ui.text>

        @if (session('status'))
            <x-ui.banner status="success" title="Seguimiento actualizado" :description="session('status')" />
        @endif

        <x-ui.card padding="4">
            @include('partials.filtros-periodo', [
                'ruta' => 'notas-cargo.movil.back.index',
                'rutaExportar' => 'notas-cargo.movil.back.exportar',
                'formId' => 'nc-movil-back-filtros',
            ])
        </x-ui.card>

        <x-ui.table label="Notas de cargo móvil en seguimiento" :truncate="true">
            <colgroup>
                <col class="col-date">
                <col class="col-ruc">
                <col class="col-client">
                <col class="col-response">
                <col class="col-response">
                <col class="col-count">
                <col class="col-count">
                <col class="col-user">
                <col class="col-action">
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">Fecha de ingreso</th>
                    <th scope="col">RUC</th>
                    <th scope="col">Razón social</th>
                    <th scope="col">Región</th>
                    <th scope="col">Modalidad</th>
                    <th scope="col" class="table__numeric">Líneas</th>
                    <th scope="col" class="table__numeric">Cargo fijo</th>
                    <th scope="col">Ejecutivo</th>
                    <th scope="col" class="table__actions">Acciones</th>
                </tr>
            </thead>
            @if ($grupos->isEmpty())
                <tbody><tr>
                    <td colspan="9">
                        <x-ui.empty-state title="No hay notas en seguimiento"
                            description="No encontramos oportunidades aprobadas para el período y estado seleccionados." compact />
                    </td>
                </tr></tbody>
            @else
                @foreach ($grupos as $estado => $grupo)
                    <x-ui.grouped-table-section id="nc-movil-back-{{ $estado }}" :label="\App\Support\EtiquetasDeDominio::texto($estado, 'estado')" :count="$grupo->count()" :columns="9">
                    @foreach ($grupo as $cargo)
                    <tr>
                        <td class="table__secondary">{{ $cargo->fecha_ingreso?->format('d/m/Y') }}</td>
                        <td class="table__primary">{{ $cargo->cliente?->ruc }}</td>
                        <td>{{ $cargo->cliente?->razon_social }}</td>
                        <td class="table__secondary">{{ $cargo->region?->nombre }}</td>
                        <td class="table__secondary">{{ \App\Support\EtiquetasDeDominio::texto($cargo->modalidad, 'modalidad') }}</td>
                        <td class="table__numeric">{{ $cargo->q_lineas }}</td>
                        <td class="table__numeric">{{ number_format((float) $cargo->cargo_fijo, 2) }}</td>
                        <td class="table__secondary">{{ $cargo->ejecutivo?->personal }}</td>
                        <td class="table__actions">
                            <x-ui.button :href="route('notas-cargo.movil.back.edit', $cargo)" variant="secondary" size="sm">
                                Actualizar
                            </x-ui.button>
                        </td>
                    </tr>
                    @endforeach
                    </x-ui.grouped-table-section>
                @endforeach
            @endif
        </x-ui.table>
    </x-ui.table-page>
@endsection
