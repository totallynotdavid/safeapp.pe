@extends('layouts.app')

@section('title', 'Gestión '.mb_strtolower($tipo))
@section('page', 'true')

@section('content')
    @php
        $tipoLabel = mb_strtolower($tipo);
        $originOptions = $origenes->map(fn ($disponible) => [
            'value' => $disponible->origen,
            'label' => $disponible->origen.' ('.$disponible->disponibles.' disponibles)',
        ])->all();
        $releaseFormId = 'release-'.$tipo.'-queue';
        $releaseDialogId = 'release-'.$tipo.'-dialog';
        $assignmentDialogId = 'assign-'.$tipo.'-dialog';
    @endphp

    <div class="page-layout layout" data-height="fill">
        <div class="page-layout__inner">
            <header class="layout-header" data-divider="true">
                <div class="layout-header__inner">
                    <div class="stack gestion-header" data-direction="horizontal" data-gap="2" data-v-align="center">
                        <div data-theme="stack-item" data-size="fill">
                            <x-ui.heading level="1">Gestión {{ $tipoLabel }}</x-ui.heading>
                        </div>
                        <div class="stack gestion-header__actions" data-direction="horizontal" data-gap="2" data-h-align="end" data-v-align="center">
                            <x-ui.icon-button :href="route($rutaBase.'.agregar')" label="Agregar cliente" variant="ghost">
                                <x-ui.icon name="user-plus" size="sm" />
                            </x-ui.icon-button>
                            <x-ui.icon-button label="Devolver pendientes" variant="ghost" data-alert-dialog-open="{{ $releaseDialogId }}">
                                <x-ui.icon name="arrow-uturn-left" size="sm" />
                            </x-ui.icon-button>
                            <x-ui.button type="button" variant="primary" :disabled="$origenes->isEmpty()"
                                         data-dialog-open="{{ $assignmentDialogId }}">
                                <x-slot:icon><x-ui.icon name="plus" size="sm" /></x-slot:icon>
                                Añadir clientes
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </header>

            <div class="page-layout__middle">
                <div class="page-layout__content-slot">
                    <div class="layout-content">
                        <div class="stack" data-direction="vertical" data-gap="4">
                            <x-ui.table-search
                                :action="route($rutaBase)"
                                :value="$busqueda"
                                placeholder="Buscar por RUC o razón social…"
                                :result-count="$datos->total()"
                                label="Buscar clientes pendientes"
                            />

                            <x-ui.table label="Clientes {{ $tipoLabel }} pendientes" :truncate="true">
                                <colgroup>
                                    <col class="col-ruc">
                                    <col class="col-client">
                                    <col class="col-action">
                                </colgroup>
                                <thead><tr>
                                    <th scope="col">RUC</th>
                                    <th scope="col">Razón social</th>
                                    <th scope="col" class="table__actions">Acciones</th>
                                </tr></thead>
                                <tbody>
                                    @forelse ($datos as $dato)
                                        <tr>
                                            <td class="table__primary">{{ $dato->cliente->ruc }}</td>
                                            <td>{{ $dato->cliente->razon_social }}</td>
                                            <td class="table__actions">
                                                <x-ui.button href="{{ route('gestion.show', $dato) }}" variant="secondary" size="sm">Gestionar</x-ui.button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">
                                                <x-ui.empty-state
                                                    :title="$busqueda === '' ? 'No tiene clientes pendientes' : 'No hay clientes que coincidan'"
                                                    :description="$busqueda === '' ? 'Su cola de la tienda activa está al día.' : 'Prueba con otro RUC o razón social.'"
                                                    compact
                                                />
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </x-ui.table>

                            @if ($datos->hasPages())
                                <x-ui.pagination :paginator="$datos" label="Páginas de clientes pendientes" />
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-ui.dialog
        :id="$assignmentDialogId"
        title="Incorporar clientes"
        :description="'Elija un origen. Incorporaremos hasta '.$lote.' clientes disponibles a su cola.'"
        :open-on-load="$errors->has('origen')">
        <form id="assign-{{ $tipo }}-queue" method="POST" action="{{ route($rutaBase.'.asignar') }}">
            @csrf
            <x-ui.selector
                id="gestion-{{ $tipo }}-origen"
                name="origen"
                label="Origen"
                :options="$originOptions"
                :value="old('origen', '')"
                placeholder="Elija un origen"
                :required="true"
                :disabled="$origenes->isEmpty()"
                :error="$errors->first('origen')"
            />
        </form>
        <x-slot:footer>
            <div class="stack" data-direction="horizontal" data-gap="2" data-h-align="end">
                <x-ui.button type="button" variant="ghost" data-dialog-close>Cancelar</x-ui.button>
                <x-ui.button type="submit" variant="primary" form="assign-{{ $tipo }}-queue" :disabled="$origenes->isEmpty()">Incorporar hasta {{ $lote }}</x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.dialog>

    <form id="{{ $releaseFormId }}" method="POST" action="{{ route($rutaBase.'.liberar') }}">
        @csrf
    </form>

    <x-ui.alert-dialog
        :id="$releaseDialogId"
        title="¿Devolver todos sus pendientes?"
        description="Los clientes {{ $tipoLabel }} que todavía no gestionó volverán a la cola y podrán asignarse a otro agente."
        action-label="Devolver pendientes"
        action-variant="destructive"
        :form="$releaseFormId"
    />
@endsection
