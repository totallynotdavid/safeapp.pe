@extends('layouts.app')

@section('title', 'Registro de quiebres')
@section('page', 'true')

@section('content')
    @php($grupos = collect($estados)->mapWithKeys(fn ($estado) => [$estado => $quiebres->where('estado', $estado)])->filter->isNotEmpty())

    {{-- Official table-page hierarchy: packages/cli/templates/pages/table-page/page.tsx @ 9da0792516e4 --}}
    <x-ui.table-page title="Mis quiebres">
        <x-slot:actions>
            <x-ui.button href="{{ route('quiebres.registro.create') }}" variant="primary">Nuevo quiebre</x-ui.button>
        </x-slot:actions>

        <x-ui.text type="supporting" color="secondary">Consulte y continúe los quiebres que registró.</x-ui.text>

        <x-ui.card padding="4">
            @include('partials.filtros-ano-estado', [
                'ruta' => 'quiebres.registro.index',
                'formId' => 'quiebres-registro-filtros',
            ])
        </x-ui.card>

        <x-ui.table label="Quiebres registrados" :truncate="true">
                                <thead><tr>
                                    <th scope="col">Fecha de registro</th>
                                    <th scope="col">RUC</th>
                                    <th scope="col">Razón social</th>
                                    <th scope="col">Servicio</th>
                                    <th scope="col" class="table__actions">Acciones</th>
                                </tr></thead>
                                @if ($grupos->isEmpty())
                                    <tbody><tr><td colspan="5">
                                        <x-ui.empty-state title="No hay quiebres" description="No encontramos registros para los filtros seleccionados." compact />
                                    </td></tr></tbody>
                                @else
                                    @foreach ($grupos as $estado => $grupo)
                                        <x-ui.grouped-table-section id="quiebres-registro-{{ $estado }}" :label="\App\Support\EtiquetasDeDominio::texto($estado, 'estado')" :count="$grupo->count()" :columns="5">
                                        @foreach ($grupo as $quiebre)
                                        <tr>
                                            <td class="table__secondary">{{ $quiebre->fecha_registro?->format('d/m/Y') }}</td>
                                            <td class="table__primary">{{ $quiebre->cliente->ruc }}</td>
                                            <td>{{ $quiebre->cliente->razon_social }}</td>
                                            <td class="table__secondary">{{ \App\Support\EtiquetasDeDominio::texto($quiebre->servicio, 'servicio') }}</td>
                                            <td class="table__actions">
                                                <x-ui.button href="{{ route('quiebres.registro.edit', $quiebre) }}" variant="secondary" size="sm">
                                                    {{ $quiebre->esEditable() ? 'Editar' : 'Ver detalle' }}
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
