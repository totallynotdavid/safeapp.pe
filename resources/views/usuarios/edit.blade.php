@extends('layouts.settings')

@section('title', 'Editar usuario')
@section('page', 'true')

@section('content')
    <x-ui.settings-page title="Editar cuenta" :description="$usuario->usuario.' · '.($usuario->rol?->nombre_rol ?? 'Sin rol')" max-width="960px">
                            <x-ui.grid :min-width="320" gap="10">
                                <x-ui.stack direction="vertical" gap="1">
                                    <x-ui.heading level="3">Datos de la cuenta</x-ui.heading>
                                    <x-ui.text type="supporting" color="secondary">{{ $usuario->usuario }} · {{ $usuario->rol?->nombre_rol ?? 'Sin rol' }}</x-ui.text>
                                </x-ui.stack>

                                <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
                                    @csrf
                                    @method('PUT')
                                    <x-ui.form-layout>
                                        <x-ui.text-input
                                            name="personal"
                                            label="Nombre completo"
                                            :value="old('personal', $usuario->personal)"
                                            :error="$errors->first('personal')"
                                            required
                                            autofocus
                                            maxlength="50"
                                        />
                                        <x-ui.text-input
                                            name="clave"
                                            label="Nueva contraseña"
                                            type="password"
                                            value=""
                                            :description="$usuario->clave === null ? 'Esta cuenta no tiene contraseña y no puede iniciar sesión hasta que le asignes una.' : 'Déjala en blanco para conservar la contraseña actual.'"
                                            :error="$errors->first('clave')"
                                            optional
                                            autocomplete="new-password"
                                        />

                                        <div class="stack" data-direction="horizontal" data-gap="2" data-h-align="start" data-v-align="center">
                                            <x-ui.button type="submit" variant="primary">Guardar</x-ui.button>
                                            <x-ui.button href="{{ route('equipo.show', $usuario) }}" variant="secondary">Cancelar</x-ui.button>
                                        </div>
                                    </x-ui.form-layout>
                                </form>
                            </x-ui.grid>
    </x-ui.settings-page>
@endsection
