@extends('layouts.settings')

@section('title', $usuario->personal)

@section('content')
    <x-ui.settings-page :title="$usuario->personal" :description="$usuario->rol?->nombre_rol ?? 'Sin rol'" max-width="960px">
        @can('usuarios.manage')
            <x-ui.card padding="6">
                <x-ui.stack direction="vertical" gap="4">
                    <x-ui.stack direction="horizontal" h-align="between" v-align="center">
                        <x-ui.heading level="2">Cuenta</x-ui.heading>
                        <x-ui.button href="{{ route('usuarios.edit', $usuario) }}" variant="secondary" size="sm">Editar cuenta</x-ui.button>
                    </x-ui.stack>
                    <x-ui.metadata-list columns="multi">
                        <x-ui.metadata-list-item label="Usuario" :value="$usuario->usuario" />
                        <x-ui.metadata-list-item label="Rol" :value="$usuario->rol?->nombre_rol ?? 'Sin rol'" />
                        <x-ui.metadata-list-item label="Estado" :value="$usuario->estado ? 'Habilitado' : 'Inhabilitado'" />
                    </x-ui.metadata-list>
                </x-ui.stack>
            </x-ui.card>
        @endcan

        @can('personal.gestionar')
            <x-ui.card padding="6">
                <x-ui.stack direction="vertical" gap="4">
                    <x-ui.stack direction="horizontal" h-align="between" v-align="center">
                        <x-ui.heading level="2">Datos personales</x-ui.heading>
                        <x-ui.button href="{{ route('personal.edit', $usuario) }}" variant="secondary" size="sm">Editar datos</x-ui.button>
                    </x-ui.stack>
                    <x-ui.metadata-list columns="multi">
                        <x-ui.metadata-list-item label="Supervisor" :value="$usuario->supervisor?->nombre ?? 'Sin supervisor'" />
                        <x-ui.metadata-list-item label="Nacimiento" :value="$usuario->fecha_nacimiento?->format('Y-m-d') ?? 'No registrado'" />
                        <x-ui.metadata-list-item label="Teléfono" :value="$usuario->telefono ?? 'No registrado'" />
                        <x-ui.metadata-list-item label="Correo" :value="$usuario->correo ?? 'No registrado'" />
                    </x-ui.metadata-list>
                </x-ui.stack>
            </x-ui.card>
        @endcan

        @can('asignacion.gestionar')
            <x-ui.card padding="6">
                <x-ui.stack direction="vertical" gap="4">
                    <x-ui.stack direction="horizontal" h-align="between" v-align="center">
                        <x-ui.heading level="2">Tiendas</x-ui.heading>
                        <x-ui.button href="{{ route('asignacion.edit', $usuario) }}" variant="secondary" size="sm">Administrar tiendas</x-ui.button>
                    </x-ui.stack>
                    <x-ui.metadata-list columns="multi">
                        <x-ui.metadata-list-item label="Tienda activa" :value="$usuario->asignacionActiva?->tienda?->nombre ?? 'Sin tienda activa'" />
                        <x-ui.metadata-list-item label="Tiendas asignadas" :value="$usuario->asignaciones->pluck('tienda.nombre')->filter()->join(', ') ?: 'Ninguna'" />
                    </x-ui.metadata-list>
                </x-ui.stack>
            </x-ui.card>
        @endcan
    </x-ui.settings-page>
@endsection
