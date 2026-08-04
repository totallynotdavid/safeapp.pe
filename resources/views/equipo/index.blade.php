@extends('layouts.settings')

@section('title', 'Equipo')

@section('content')
    <x-ui.settings-page title="Equipo" description="Administra las cuentas, los datos personales y las tiendas del equipo.">
        <x-slot:actions>
            @can('usuarios.manage')
                <x-ui.button href="{{ route('usuarios.create') }}" variant="primary" size="lg">
                    <x-slot:icon><x-ui.icon name="plus" /></x-slot:icon>
                    Crear usuario
                </x-ui.button>
            @endcan
        </x-slot:actions>

        <div class="team-table">
        <x-ui.table label="Equipo" :truncate="true">
            <thead><tr>
                <th scope="col">Persona</th>
                @can('usuarios.manage')<th scope="col">Cuenta</th><th scope="col">Rol</th><th scope="col">Estado</th>@endcan
                @can('personal.gestionar')<th scope="col">Contacto</th>@endcan
                @can('asignacion.gestionar')<th scope="col">Tienda activa</th>@endcan
                <th scope="col" class="table__actions">Acciones</th>
            </tr></thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr>
                        <td><div class="stack" data-direction="horizontal" data-gap="3"><x-ui.avatar :name="$usuario->personal" size="xsmall" /><span class="table__primary">{{ $usuario->personal }}</span></div></td>
                        @can('usuarios.manage')
                            <td>{{ $usuario->usuario }}</td>
                            <td class="table__secondary">{{ $usuario->rol?->nombre_rol ?? 'Sin rol' }}</td>
                            <td><div class="stack" data-direction="horizontal" data-gap="2" data-v-align="center"><x-ui.status-dot :variant="$usuario->estado ? 'success' : 'neutral'" :label="$usuario->estado ? 'Habilitado' : 'Inhabilitado'" /><span>{{ $usuario->estado ? 'Habilitado' : 'Inhabilitado' }}</span></div></td>
                        @endcan
                        @can('personal.gestionar')
                            <td><div>{{ $usuario->correo ?: 'Sin correo' }}</div><div class="table__secondary">{{ $usuario->telefono ?: 'Sin teléfono' }}</div></td>
                        @endcan
                        @can('asignacion.gestionar')<td>{{ $usuario->asignacionActiva?->tienda?->nombre ?? 'Sin tienda activa' }}</td>@endcan
                        <td class="table__actions"><x-ui.button href="{{ route('equipo.show', $usuario) }}" variant="secondary" size="sm">Ver persona</x-ui.button></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-ui.empty-state title="No hay integrantes" compact /></td></tr>
                @endforelse
            </tbody>
        </x-ui.table>
        </div>

        <x-ui.list density="spacious" dividers class="team-mobile-list" aria-label="Equipo">
            @foreach ($usuarios as $usuario)
                @php
                    $teamDescription = auth()->user()->can('usuarios.manage')
                        ? (($usuario->rol?->nombre_rol ?? 'Sin rol').' · '.($usuario->estado ? 'Habilitado' : 'Inhabilitado'))
                        : (auth()->user()->can('personal.gestionar')
                            ? ($usuario->correo ?: $usuario->telefono ?: 'Sin datos de contacto')
                            : ($usuario->asignacionActiva?->tienda?->nombre ?? 'Sin tienda activa'));
                @endphp
                <x-ui.list-item :label="$usuario->personal" :description="$teamDescription" :href="route('equipo.show', $usuario)">
                    <x-slot:start><x-ui.avatar :name="$usuario->personal" size="xsmall" /></x-slot:start>
                    <x-slot:end><x-ui.icon name="chevron-right" size="sm" color="secondary" /></x-slot:end>
                </x-ui.list-item>
            @endforeach
        </x-ui.list>
    </x-ui.settings-page>
@endsection
