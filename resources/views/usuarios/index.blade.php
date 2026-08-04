@extends('layouts.settings')

@section('title', 'Usuarios')
@section('page', 'true')

@section('content')
    <x-ui.settings-page title="Cuentas" description="Vista de compatibilidad para administrar cuentas individuales.">
        <x-slot:actions>
            <x-ui.button href="{{ route('usuarios.create') }}" variant="primary" size="lg">
                <x-slot:icon><x-ui.icon name="plus" /></x-slot:icon>
                Crear usuario
            </x-ui.button>
        </x-slot:actions>
                        <x-ui.table label="Usuarios" :truncate="true">
                            <colgroup>
                                <col class="col-user">
                                <col class="col-name">
                                <col class="col-role">
                                <col class="col-status">
                                <col class="col-actions">
                            </colgroup>
                            <thead><tr>
                                <th scope="col">Usuario</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Rol</th>
                                <th scope="col">Estado</th>
                                <th scope="col" class="table__actions">Acciones</th>
                            </tr></thead>
                            <tbody>
                                @forelse ($usuarios as $usuario)
                                    <tr>
                                        <td class="table__primary">{{ $usuario->usuario }}</td>
                                        <td>
                                            <div class="stack" data-direction="horizontal" data-gap="3">
                                                <x-ui.avatar :name="$usuario->personal" size="xsmall" />
                                                <span>{{ $usuario->personal }}</span>
                                            </div>
                                        </td>
                                        <td class="table__secondary">{{ $usuario->rol?->nombre_rol ?? 'Sin rol' }}</td>
                                        <td>
                                            <div class="stack" data-direction="horizontal" data-gap="2" data-v-align="center">
                                                <x-ui.status-dot :variant="$usuario->estado ? 'success' : 'neutral'" :label="$usuario->estado ? 'Habilitado' : 'Inhabilitado'" />
                                                <span>{{ $usuario->estado ? 'Habilitado' : 'Inhabilitado' }}</span>
                                            </div>
                                        </td>
                                        <td class="table__actions">
                                            <div class="stack" data-direction="horizontal" data-gap="2" data-h-align="end" data-v-align="center">
                                                <x-ui.button href="{{ route('usuarios.edit', $usuario) }}" variant="secondary" size="sm">Editar</x-ui.button>
                                                @unless ($usuario->id_usuario === auth()->id())
                                                    <form method="POST" action="{{ route('usuarios.estado', $usuario) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <x-ui.button type="submit" :variant="$usuario->estado ? 'destructive' : 'secondary'" size="sm">
                                                            {{ $usuario->estado ? 'Inhabilitar' : 'Habilitar' }}
                                                        </x-ui.button>
                                                    </form>
                                                @endunless
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><x-ui.empty-state title="No hay usuarios" compact /></td></tr>
                                @endforelse
                            </tbody>
                        </x-ui.table>
    </x-ui.settings-page>
@endsection
