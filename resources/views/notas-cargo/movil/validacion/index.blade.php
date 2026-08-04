@extends('layouts.app')

@section('title', 'Validación móvil')
@section('page', true)

@section('content')
    @php
        $yearOptions = collect($anos)->map(fn ($option) => [
            'value' => $option,
            'label' => (string) $option,
        ])->all();
    @endphp

    <x-ui.table-page title="Notas de cargo móvil por validar">
        <x-ui.text type="supporting" color="secondary">
            Revise las oportunidades que aún esperan una decisión del equipo validador.
        </x-ui.text>

        @if (session('status'))
            <x-ui.banner status="success" title="Validación registrada" :description="session('status')" />
        @endif

        <x-ui.card padding="4">
            {{-- An undecided case has not reached back office, so estado remains
                 PENDIENTE and a status filter would contain only one value. --}}
            <form method="GET" action="{{ route('notas-cargo.movil.validacion.index') }}">
                <x-ui.stack direction="horizontal" gap="3" v-align="end" wrap="wrap">
                    <x-ui.selector id="nc-movil-validacion-ano" name="ano" label="Año"
                        :options="$yearOptions" :value="$ano" />
                    <x-ui.button type="submit" variant="secondary">Aplicar filtro</x-ui.button>
                </x-ui.stack>
            </form>
        </x-ui.card>

        <x-ui.table label="Notas de cargo móvil por validar" :truncate="true">
            <colgroup>
                <col class="col-date">
                <col class="col-ruc">
                <col class="col-client">
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
                    <th scope="col">Modalidad</th>
                    <th scope="col" class="table__numeric">Líneas</th>
                    <th scope="col" class="table__numeric">Cargo fijo</th>
                    <th scope="col">Ejecutivo</th>
                    <th scope="col" class="table__actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cargos as $cargo)
                    <tr>
                        <td class="table__secondary">{{ $cargo->fecha_ingreso?->format('d/m/Y') }}</td>
                        <td class="table__primary">{{ $cargo->cliente?->ruc }}</td>
                        <td>{{ $cargo->cliente?->razon_social }}</td>
                        <td class="table__secondary">{{ \App\Support\EtiquetasDeDominio::texto($cargo->modalidad, 'modalidad') }}</td>
                        <td class="table__numeric">{{ $cargo->q_lineas }}</td>
                        <td class="table__numeric">{{ number_format((float) $cargo->cargo_fijo, 2) }}</td>
                        <td class="table__secondary">{{ $cargo->ejecutivo?->personal }}</td>
                        <td class="table__actions">
                            <x-ui.button :href="route('notas-cargo.movil.validacion.edit', $cargo)" variant="secondary" size="sm">
                                Revisar
                            </x-ui.button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <x-ui.empty-state title="No hay notas por validar"
                                description="No encontramos oportunidades pendientes para el año seleccionado." compact />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.table-page>
@endsection
