@extends('layouts.settings')

@section('title', 'Crear usuario')
@section('page', 'true')

@section('content')
    @php
        $roleOptions = $roles->map(static fn ($rol): array => [
            'value' => $rol->id_rol,
            'label' => $rol->nombre_rol,
        ])->all();
        $sexOptions = [
            ['value' => 'M', 'label' => 'Masculino'],
            ['value' => 'F', 'label' => 'Femenino'],
        ];
    @endphp

    <x-ui.settings-page title="Crear integrante" description="Crea las credenciales y asigna la plantilla inicial de acceso." max-width="960px">
                            <x-ui.grid :min-width="320" gap="10">
                                <x-ui.stack direction="vertical" gap="1">
                                    <x-ui.heading level="3">Datos de la cuenta</x-ui.heading>
                                    <x-ui.text type="supporting" color="secondary">Crea las credenciales y asigna la plantilla inicial de acceso.</x-ui.text>
                                </x-ui.stack>

                                <form method="POST" action="{{ route('usuarios.store') }}">
                                    @csrf
                                    <x-ui.form-layout>
                                        <x-ui.text-input
                                            name="usuario"
                                            label="Usuario"
                                            :value="old('usuario')"
                                            :error="$errors->first('usuario')"
                                            required
                                            autofocus
                                            maxlength="50"
                                            autocomplete="username"
                                        />
                                        <x-ui.text-input
                                            name="personal"
                                            label="Nombre completo"
                                            :value="old('personal')"
                                            :error="$errors->first('personal')"
                                            required
                                            maxlength="50"
                                            autocomplete="name"
                                        />
                                        <x-ui.text-input
                                            name="clave"
                                            label="Contraseña"
                                            type="password"
                                            value=""
                                            description="Usa al menos 6 caracteres."
                                            :error="$errors->first('clave')"
                                            required
                                            minlength="6"
                                            autocomplete="new-password"
                                        />
                                        <x-ui.selector
                                            name="id_rol"
                                            label="Rol"
                                            :options="$roleOptions"
                                            :value="old('id_rol', '')"
                                            placeholder="Seleccionar un rol…"
                                            :error="$errors->first('id_rol')"
                                            required
                                        />
                                        <x-ui.radio-list
                                            name="sexo"
                                            label="Sexo"
                                            :options="$sexOptions"
                                            :value="old('sexo', '')"
                                            :error="$errors->first('sexo')"
                                            orientation="horizontal"
                                            required
                                        />

                                        <div class="stack" data-direction="horizontal" data-gap="2" data-h-align="start" data-v-align="center">
                                            <x-ui.button type="submit" variant="primary">Crear</x-ui.button>
                                            <x-ui.button href="{{ route('equipo.index') }}" variant="secondary">Cancelar</x-ui.button>
                                        </div>
                                    </x-ui.form-layout>
                                </form>
                            </x-ui.grid>
    </x-ui.settings-page>
@endsection
