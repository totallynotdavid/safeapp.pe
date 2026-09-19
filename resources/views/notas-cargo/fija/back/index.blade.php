@extends('layouts.app')

@section('title', 'Back fija')
@section('page', true)

@section('content')
    @php
        $grupos = collect($estados)->mapWithKeys(fn ($estado) => [$estado => $cargos->where('estado', $estado)])->filter->isNotEmpty();
    @endphp
    <x-ui.table-page title="Notas de cargo fija en seguimiento">
        <x-ui.text type="supporting" color="secondary">
            Actualice y exporte las solicitudes aprobadas que avanzan por back office.
        </x-ui.text>

        @if (session('status'))
            <x-ui.banner status="success" title="Seguimiento actualizado" :description="session('status')" />
        @endif

        <x-ui.card padding="4">
            @include('partials.filtros-periodo', [
                'ruta' => 'notas-cargo.fija.back.index',
                'rutaExportar' => 'notas-cargo.fija.back.exportar',
                'formId' => 'nc-fija-back-filtros',
            ])
        </x-ui.card>

        <x-ui.table label="Notas de cargo fija en seguimiento" :truncate="true">
            <colgroup>
                <col class="col-date">
                <col class="col-ruc">
                <col class="col-client">
                <col class="col-response">
                <col class="col-response">
                <col class="col-user">
                <col class="col-action">
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">Fecha de ingreso</th>
                    <th scope="col">RUC</th>
                    <th scope="col">Razón social</th>
                    <th scope="col">Producto</th>
                    <th scope="col">Región</th>
                    <th scope="col">Ejecutivo</th>
                    <th scope="col" class="table__actions">Acciones</th>
                </tr>
            </thead>
            @if ($grupos->isEmpty())
                <tbody><tr>
                    <td colspan="7">
                        <x-ui.empty-state title="No hay notas en seguimiento"
                            description="No encontramos solicitudes aprobadas para el período y estado seleccionados." compact />
                    </td>
                </tr></tbody>
            @else
                @foreach ($grupos as $estado => $grupo)
                    <x-ui.grouped-table-section id="nc-fija-back-{{ $estado }}" :label="\App\Support\EtiquetasDeDominio::texto($estado, 'estado')" :count="$grupo->count()" :columns="7">
                    @foreach ($grupo as $cargo)
                    <tr>
                        <td class="table__secondary">{{ $cargo->fecha_ingreso?->format('d/m/Y') }}</td>
                        <td class="table__primary">{{ $cargo->cliente?->ruc }}</td>
                        <td>{{ $cargo->cliente?->razon_social }}</td>
                        <td class="table__secondary">{{ trim($cargo->subtipo.' '.$cargo->descripcion) }}</td>
                        <td class="table__secondary">{{ $cargo->region?->nombre }}</td>
                        <td class="table__secondary">{{ $cargo->ejecutivo?->personal }}</td>
                        <td class="table__actions">
                            <x-ui.button :href="route('notas-cargo.fija.back.edit', $cargo)" variant="secondary" size="sm">
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
