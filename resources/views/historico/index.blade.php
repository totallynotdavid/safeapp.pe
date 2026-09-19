@extends('layouts.app')

@section('title', 'Histórico')
@section('page', 'true')

@section('content')
    <x-ui.table-page title="Mis gestiones">
        <x-slot:actions>
            <x-ui.icon-button
                type="submit"
                label="Exportar gestiones"
                variant="ghost"
                form="historico-filtros"
                formaction="{{ route('historico.exportar') }}"
            >
                <x-ui.icon name="arrow-down-tray" />
            </x-ui.icon-button>
        </x-slot:actions>

        <x-ui.text type="supporting" color="secondary">
            Consulta y exporta los contactos que registraste en el período elegido.
        </x-ui.text>

        {{-- The export button in the page actions submits this form by id, so it exports the range shown. --}}
        <x-ui.stack
            id="historico-filtros"
            as="form"
            method="GET"
            action="{{ route('historico.index') }}"
            direction="horizontal"
            v-align="end"
            gap="3"
            wrap="wrap"
        >
            <input type="hidden" name="buscar" value="{{ $busqueda }}">
            <x-ui.date-input
                name="desde"
                label="Desde"
                :value="$rango->desde->toDateString()"
                :max="$rango->hasta->toDateString()"
                required
            />
            <x-ui.date-input
                name="hasta"
                label="Hasta"
                :value="$rango->hasta->toDateString()"
                :min="$rango->desde->toDateString()"
                required
            />
            <x-ui.button type="submit" variant="primary">Filtrar</x-ui.button>
        </x-ui.stack>

        <x-ui.table-search
            :action="route('historico.index')"
            :value="$busqueda"
            placeholder="Buscar por cliente, RUC, contacto o teléfono…"
            :result-count="$contactos->total()"
            label="Buscar en mis gestiones"
            :preserve="['desde' => $rango->desde->toDateString(), 'hasta' => $rango->hasta->toDateString()]"
        />

        @php
            $contactosPorDia = $contactos->getCollection()->groupBy(
                fn ($contacto) => $contacto->fecha?->toDateString() ?? 'sin-fecha',
            );
            $agruparPorDia = $contactosPorDia->count() > 1;
        @endphp

        <x-ui.table label="Histórico de mis gestiones" :truncate="true">
            <colgroup>
                <col class="col-client">
                <col class="col-contact">
                <col class="col-response">
                <col class="col-date">
                <col class="col-phone">
                <col class="col-comment">
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">Cliente</th>
                    <th scope="col">Contacto</th>
                    <th scope="col">Respuesta</th>
                    <th scope="col">{{ $agruparPorDia ? 'Hora' : 'Fecha' }}</th>
                    <th scope="col">Teléfono</th>
                    <th scope="col">Comentario</th>
                </tr>
            </thead>
            @if ($contactos->isEmpty())
                <tbody>
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state
                                title="No hay gestiones en este período"
                                description="Prueba con un rango de fechas diferente."
                                compact
                            />
                        </td>
                    </tr>
                </tbody>
            @elseif ($agruparPorDia)
                @foreach ($contactosPorDia as $dia => $contactosDelDia)
                    <x-ui.grouped-table-section
                        id="historico-dia-{{ $dia }}"
                        :label="$dia === 'sin-fecha' ? 'Sin fecha registrada' : \Illuminate\Support\Carbon::parse($dia)->format('d/m/Y')"
                        :count="$contactosDelDia->count()"
                        :columns="6"
                    >
                        @foreach ($contactosDelDia as $contacto)
                            <tr>
                                <td>
                                    <div class="table__primary">{{ $contacto->razon_social }}</div>
                                    <div class="table__secondary">{{ $contacto->cliente?->ruc ?? '—' }}</div>
                                </td>
                                <td>{{ $contacto->gestion ?? '—' }}</td>
                                <td>{{ $contacto->respuesta?->descripcion ?? '—' }}</td>
                                <td class="table__secondary">{{ $contacto->fecha?->format('H:i') ?? '—' }}</td>
                                <td>{{ $contacto->telefono ?? '—' }}</td>
                                <td>{{ $contacto->comentario ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </x-ui.grouped-table-section>
                @endforeach
            @else
                <tbody>
                    @foreach ($contactos as $contacto)
                        <tr>
                            <td>
                                <div class="table__primary">{{ $contacto->razon_social }}</div>
                                <div class="table__secondary">{{ $contacto->cliente?->ruc ?? '—' }}</div>
                            </td>
                            <td>{{ $contacto->gestion ?? '—' }}</td>
                            <td>{{ $contacto->respuesta?->descripcion ?? '—' }}</td>
                            <td class="table__secondary">{{ $contacto->fecha?->format('Y-m-d H:i') }}</td>
                            <td>{{ $contacto->telefono ?? '—' }}</td>
                            <td>{{ $contacto->comentario ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            @endif
        </x-ui.table>

        @if ($contactos->hasPages())
            <x-ui.pagination :paginator="$contactos" label="Páginas del histórico de gestiones" />
        @endif
    </x-ui.table-page>
@endsection
