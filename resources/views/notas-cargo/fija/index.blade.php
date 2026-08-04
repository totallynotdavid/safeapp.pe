@extends('layouts.app')

@section('title', 'Notas de cargo fija')
@section('page', true)

@section('content')
    @php
        $grupos = collect($estados)->mapWithKeys(fn ($estado) => [$estado => $cargos->where('estado', $estado)])->filter->isNotEmpty();
    @endphp
    <x-ui.table-page title="Mis notas de cargo fija">
        <x-slot:actions>
            <x-ui.button :href="route('notas-cargo.fija.create')" variant="primary">
                <x-slot:icon><x-ui.icon name="plus" /></x-slot:icon>
                Nueva nota de cargo
            </x-ui.button>
        </x-slot:actions>

        <x-ui.text type="supporting" color="secondary">
            Consulte, exporte y continúe las solicitudes fijas que registró.
        </x-ui.text>

        <x-ui.card padding="4">
            @include('partials.filtros-periodo', [
                'ruta' => 'notas-cargo.fija.index',
                'rutaExportar' => 'notas-cargo.fija.exportar',
                'formId' => 'nc-fija-registro-filtros',
            ])
        </x-ui.card>

        <x-ui.table label="Mis notas de cargo fija" :truncate="true">
            <colgroup>
                <col class="col-date">
                <col class="col-ruc">
                <col class="col-client">
                <col class="col-response">
                <col class="col-count">
                <col class="col-status">
                <col class="col-action">
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">Fecha de ingreso</th>
                    <th scope="col">RUC</th>
                    <th scope="col">Razón social</th>
                    <th scope="col">Producto</th>
                    <th scope="col" class="table__numeric">Cargo fijo</th>
                    <th scope="col">Validación</th>
                    <th scope="col" class="table__actions">Acciones</th>
                </tr>
            </thead>
            @if ($grupos->isEmpty())
                <tbody><tr>
                    <td colspan="7">
                        <x-ui.empty-state title="No hay notas de cargo fija"
                            description="No encontramos solicitudes para el período y estado seleccionados." compact />
                    </td>
                </tr></tbody>
            @else
                @foreach ($grupos as $estado => $grupo)
                    <x-ui.grouped-table-section id="nc-fija-{{ $estado }}" :label="\App\Support\EtiquetasDeDominio::texto($estado, 'estado')" :count="$grupo->count()" :columns="7">
                    @foreach ($grupo as $cargo)
                    <tr>
                        <td class="table__secondary">{{ $cargo->fecha_ingreso?->format('d/m/Y') }}</td>
                        <td class="table__primary">{{ $cargo->cliente?->ruc }}</td>
                        <td>{{ $cargo->cliente?->razon_social }}</td>
                        <td class="table__secondary">{{ trim($cargo->subtipo.' '.$cargo->descripcion) }}</td>
                        <td class="table__numeric">{{ number_format((float) $cargo->cargo_fijo, 2) }}</td>
                        <td class="table__secondary">{{ \App\Support\EtiquetasDeDominio::texto($cargo->validacion, 'validacion') ?: '—' }}</td>
                        <td class="table__actions">
                            <x-ui.button :href="route('notas-cargo.fija.edit', $cargo)" variant="secondary" size="sm">
                                {{ $cargo->esEditable() ? 'Editar' : 'Ver detalle' }}
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
