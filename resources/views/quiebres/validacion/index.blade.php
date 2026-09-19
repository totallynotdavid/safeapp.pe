@extends('layouts.app')

@section('title', 'Validación de quiebres')
@section('page', 'true')

@section('content')
    @php
        $grupos = collect($estados)->mapWithKeys(fn ($estado) => [$estado => $quiebres->where('estado', $estado)])->filter->isNotEmpty();
    @endphp

    <div class="page-layout layout" data-height="fill">
        <div class="page-layout__inner">
            <header class="layout-header" data-divider="true">
                <div class="layout-header__inner">
                    <div class="stack" data-direction="vertical" data-gap="1">
                        <x-ui.heading level="1">Quiebres por validar</x-ui.heading>
                        <x-ui.text type="supporting" color="secondary">Revise los casos correspondientes a su tienda asignada.</x-ui.text>
                    </div>
                </div>
            </header>

            <div class="page-layout__middle">
                <div class="page-layout__content-slot">
                    <div class="layout-content">
                        @if ($sinAsignacion)
                            <x-ui.card padding="4">
                                <x-ui.empty-state
                                    title="No tienes una tienda asignada"
                                    description="Solicite a un supervisor una tienda para poder revisar quiebres."
                                />
                            </x-ui.card>
                        @else
                            <div class="stack" data-direction="vertical" data-gap="4">
                                <x-ui.card padding="4">
                                    @include('partials.filtros-ano-estado', [
                                        'ruta' => 'quiebres.validacion.index',
                                        'formId' => 'quiebres-validacion-filtros',
                                    ])
                                </x-ui.card>

                                <x-ui.table label="Quiebres por validar" :truncate="true">
                                    <thead><tr>
                                        <th scope="col">Fecha de registro</th>
                                        <th scope="col">RUC</th>
                                        <th scope="col">Razón social</th>
                                        <th scope="col">Servicio</th>
                                        <th scope="col">Back</th>
                                        <th scope="col" class="table__actions">Acciones</th>
                                    </tr></thead>
                                    @if ($grupos->isEmpty())
                                        <tbody><tr><td colspan="6">
                                            <x-ui.empty-state title="No hay quiebres por validar" description="No encontramos casos para los filtros seleccionados." compact />
                                        </td></tr></tbody>
                                    @else
                                        @foreach ($grupos as $estado => $grupo)
                                            <x-ui.grouped-table-section id="quiebres-validacion-{{ $estado }}" :label="\App\Support\EtiquetasDeDominio::texto($estado, 'estado')" :count="$grupo->count()" :columns="6">
                                            @foreach ($grupo as $quiebre)
                                            <tr>
                                                <td class="table__secondary">{{ $quiebre->fecha_registro?->format('d/m/Y') }}</td>
                                                <td class="table__primary">{{ $quiebre->cliente->ruc }}</td>
                                                <td>{{ $quiebre->cliente->razon_social }}</td>
                                                <td class="table__secondary">{{ \App\Support\EtiquetasDeDominio::texto($quiebre->servicio, 'servicio') }}</td>
                                                <td class="table__secondary">{{ $quiebre->back ?: '—' }}</td>
                                                <td class="table__actions">
                                                    <x-ui.button href="{{ route('quiebres.validacion.edit', $quiebre) }}" variant="secondary" size="sm">Revisar</x-ui.button>
                                                </td>
                                            </tr>
                                            @endforeach
                                            </x-ui.grouped-table-section>
                                        @endforeach
                                    @endif
                                </x-ui.table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
