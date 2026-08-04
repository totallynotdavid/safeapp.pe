@extends('layouts.app')

@section('title', 'Gestión diaria')
@section('page', 'true')

@section('content')
    @php
        $supervisorOptions = $supervisores->map(fn ($opcion) => [
            'value' => (string) $opcion->id_supervisor,
            'label' => $opcion->nombre,
        ])->all();
    @endphp

    <x-ui.table-page title="Gestión del equipo">
        <x-slot:actions>
            <x-ui.icon-button
                type="submit"
                label="Exportar gestión del equipo"
                variant="ghost"
                form="seguimiento-filtros"
                formaction="{{ route('seguimiento.exportar') }}"
            >
                <x-ui.icon name="arrow-down-tray" />
            </x-ui.icon-button>
        </x-slot:actions>

        <x-ui.text type="supporting" color="secondary">
            Revisa y exporta los contactos registrados por el equipo de un supervisor.
        </x-ui.text>

        {{-- The visible supervisor/range and export remain one GET form. --}}
        <x-ui.stack
            id="seguimiento-filtros"
            as="form"
            method="GET"
            action="{{ route('seguimiento.index') }}"
            direction="horizontal"
            v-align="end"
            gap="3"
            wrap="wrap"
        >
            <input type="hidden" name="buscar" value="{{ $busqueda }}">
            <x-ui.selector
                name="supervisor"
                label="Supervisor"
                :options="$supervisorOptions"
                :value="$supervisor === null ? '' : (string) $supervisor"
                placeholder="Elegir supervisor"
            />
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

        @if ($supervisor !== null)
            <x-ui.table-search
                :action="route('seguimiento.index')"
                :value="$busqueda"
                placeholder="Buscar por cliente, RUC, contacto o teléfono…"
                :result-count="$contactos->total()"
                label="Buscar en la gestión del equipo"
                :preserve="[
                    'supervisor' => (string) $supervisor,
                    'desde' => $rango->desde->toDateString(),
                    'hasta' => $rango->hasta->toDateString(),
                ]"
            />
        @endif

        @php
            $contactosPorDia = $contactos->getCollection()->groupBy(
                fn ($contacto) => $contacto->fecha?->toDateString() ?? 'sin-fecha',
            );
            $agruparPorDia = $contactosPorDia->count() > 1;
        @endphp

        <x-ui.table label="Gestión diaria del equipo" :truncate="true">
            <colgroup>
                <col class="col-client">
                <col class="col-contact">
                <col class="col-response">
                <col class="col-name">
                <col class="col-date">
                <col class="col-comment">
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">Cliente</th>
                    <th scope="col">Contacto</th>
                    <th scope="col">Respuesta</th>
                    <th scope="col">Personal</th>
                    <th scope="col">{{ $agruparPorDia ? 'Hora' : 'Fecha' }}</th>
                    <th scope="col">Comentario</th>
                </tr>
            </thead>
            @if ($contactos->isEmpty())
                <tbody>
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state
                                :title="$supervisor === null ? 'Elige un supervisor' : 'No hay gestiones en este período'"
                                :description="$supervisor === null ? 'Selecciona un equipo para consultar su actividad.' : 'Prueba con un rango de fechas diferente.'"
                                compact
                            />
                        </td>
                    </tr>
                </tbody>
            @elseif ($agruparPorDia)
                @foreach ($contactosPorDia as $dia => $contactosDelDia)
                    <x-ui.grouped-table-section
                        id="seguimiento-dia-{{ $dia }}"
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
                                <td class="table__primary">{{ $contacto->agente?->personal ?? '—' }}</td>
                                <td class="table__secondary">{{ $contacto->fecha?->format('H:i') ?? '—' }}</td>
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
                            <td class="table__primary">{{ $contacto->agente?->personal ?? '—' }}</td>
                            <td class="table__secondary">{{ $contacto->fecha?->format('Y-m-d H:i') }}</td>
                            <td>{{ $contacto->comentario ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            @endif
        </x-ui.table>

        @if ($contactos->hasPages())
            <x-ui.pagination :paginator="$contactos" label="Páginas de gestión del equipo" />
        @endif
    </x-ui.table-page>
@endsection
