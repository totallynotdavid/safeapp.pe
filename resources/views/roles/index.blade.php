@extends('layouts.settings')

@section('title', 'Roles')
@section('page', 'true')

@section('content')
    <x-ui.settings-page title="Roles y permisos" description="Define las plantillas de acceso del equipo.">
        <x-slot:actions>
            <x-ui.button href="{{ route('roles.create') }}" variant="primary" size="lg">
                <x-slot:icon><x-ui.icon name="plus" /></x-slot:icon>
                Crear rol
            </x-ui.button>
        </x-slot:actions>
                        <x-ui.table label="Roles y permisos">
                            <colgroup><col><col class="col-count"><col class="col-count"><col class="col-actions"></colgroup>
                            <thead><tr>
                                <th scope="col">Rol</th>
                                <th scope="col">Usuarios</th>
                                <th scope="col">Permisos</th>
                                <th scope="col" class="table__actions">Acciones</th>
                            </tr></thead>
                            <tbody>
                                @forelse ($roles as $rol)
                                    @php
                                        $deleteFormId = 'delete-role-'.$rol->getKey();
                                        $deleteDialogId = 'delete-role-'.$rol->getKey().'-dialog';
                                    @endphp
                                    <tr>
                                        <td class="table__primary">{{ $rol->nombre_rol }}</td>
                                        <td><x-ui.badge>{{ $rol->usuarios_count }}</x-ui.badge></td>
                                        <td><x-ui.badge>{{ $rol->menus_count }}</x-ui.badge></td>
                                        <td class="table__actions">
                                            <div class="stack" data-direction="horizontal" data-gap="2" data-h-align="end" data-v-align="center">
                                                <x-ui.button href="{{ route('roles.edit', $rol) }}" variant="secondary" size="sm">Editar</x-ui.button>
                                                <form id="{{ $deleteFormId }}" method="POST" action="{{ route('roles.destroy', $rol) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                <x-ui.button type="button" variant="destructive" size="sm" :disabled="$rol->usuarios_count > 0"
                                                    data-alert-dialog-open="{{ $deleteDialogId }}"
                                                    aria-haspopup="dialog"
                                                        title="{{ $rol->usuarios_count > 0 ? 'Tiene usuarios asignados' : '' }}">
                                                    Eliminar
                                                </x-ui.button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4"><x-ui.empty-state title="No hay roles" compact /></td></tr>
                                @endforelse
                            </tbody>
                        </x-ui.table>

                        @foreach ($roles as $rol)
                            @if ($rol->usuarios_count === 0)
                                <x-ui.alert-dialog
                                    id="delete-role-{{ $rol->getKey() }}-dialog"
                                    title="¿Eliminar el rol {{ $rol->nombre_rol }}?"
                                    description="El rol se eliminará de forma permanente. Esta acción no se puede deshacer."
                                    action-label="Eliminar rol"
                                    action-variant="destructive"
                                    form="delete-role-{{ $rol->getKey() }}"
                                />
                            @endif
                        @endforeach
    </x-ui.settings-page>
@endsection
