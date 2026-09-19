@extends('layouts.settings')

@section('title', 'Datos del personal')
@section('page', 'true')

@section('content')
    <x-ui.settings-page title="Datos personales" description="Vista de compatibilidad para administrar los datos de contacto del equipo.">

                            <x-ui.table label="Personal" :truncate="true">
                            <colgroup><col class="col-name"><col><col><col><col><col><col class="col-action"></colgroup>
                            <thead><tr>
                                <th scope="col">Ejecutivo</th><th scope="col">Rol</th><th scope="col">Supervisor</th>
                                <th scope="col">Nacimiento</th><th scope="col">Teléfono</th><th scope="col">Correo</th>
                                <th scope="col" class="table__actions">Acciones</th>
                            </tr></thead>
                            <tbody>
                                @forelse ($usuarios as $usuario)
                                    <tr>
                                        <td>
                                            <div class="stack" data-direction="horizontal" data-gap="3">
                                                <x-ui.avatar :name="$usuario->personal" size="small" />
                                                <div>
                                                    <div class="table__primary">{{ $usuario->personal }}</div>
                                                    <div class="table__secondary">{{ $usuario->rol?->nombre_rol ?? 'Sin rol' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $usuario->rol?->nombre_rol ?? '—' }}</td>
                                        <td>{{ $usuario->supervisor?->nombre ?? '—' }}</td>
                                        <td>{{ $usuario->fecha_nacimiento?->format('Y-m-d') ?? '—' }}</td>
                                        <td>{{ $usuario->telefono ?? '—' }}</td>
                                        <td>{{ $usuario->correo ?? '—' }}</td>
                                        <td class="table__actions"><x-ui.button href="{{ route('personal.edit', $usuario) }}" variant="secondary" size="sm">Editar</x-ui.button></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7"><x-ui.empty-state title="No hay personal activo" compact /></td></tr>
                                @endforelse
                            </tbody>
                            </x-ui.table>
    </x-ui.settings-page>
@endsection
