@extends('layouts.settings')

@section('title', 'Asignación de tiendas')
@section('page', 'true')

@section('content')
    <x-ui.settings-page
        title="Asignación de tiendas"
        description="Administra la tienda asignada a cada ejecutivo."
    >
        <x-ui.table label="Asignaciones de tienda" :truncate="true">
            <colgroup>
                <col>
                <col>
                <col class="col-action">
            </colgroup>

            <thead>
                <tr>
                    <th scope="col">Ejecutivo</th>
                    <th scope="col">Tienda activa</th>
                    <th scope="col" class="table__actions">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr>
                        <td>
                            <div class="stack" data-direction="horizontal" data-gap="3">
                                <x-ui.avatar :name="$usuario->personal" size="xsmall" />
                                <span class="table__primary">{{ $usuario->personal }}</span>
                            </div>
                        </td>

                        <td class="table__secondary">
                            {{ $usuario->asignacionActiva?->tienda?->nombre ?? 'Sin tienda activa' }}
                        </td>

                        <td class="table__actions">
                            <x-ui.button
                                href="{{ route('asignacion.edit', $usuario) }}"
                                variant="secondary"
                                size="sm"
                            >
                                Administrar
                            </x-ui.button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <x-ui.empty-state title="No hay personal activo" compact />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.settings-page>
@endsection
