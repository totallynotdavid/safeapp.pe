@extends('layouts.settings')

@section('title', 'Editar datos personales')
@section('page', 'true')

@section('content')
    <x-ui.settings-page title="Editar datos personales" :description="$usuario->usuario.' · '.($usuario->rol?->nombre_rol ?? 'Sin rol')" max-width="960px">
                            <x-ui.grid :min-width="320" gap="10">
                                <x-ui.stack direction="vertical" gap="1">
                                    <x-ui.heading level="3">Datos personales</x-ui.heading>
                                    <x-ui.text type="supporting" color="secondary">
                                        Actualiza la información de contacto de {{ $usuario->usuario }} · {{ $usuario->rol?->nombre_rol ?? 'Sin rol' }}.
                                    </x-ui.text>
                                </x-ui.stack>

                                <form method="POST" action="{{ route('personal.update', $usuario) }}">
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
                                        <x-ui.date-input
                                            name="fecha_nacimiento"
                                            label="Fecha de nacimiento"
                                            :value="old('fecha_nacimiento', $usuario->fecha_nacimiento?->format('Y-m-d'))"
                                            :error="$errors->first('fecha_nacimiento')"
                                            optional
                                            has-clear
                                        />
                                        <x-ui.text-input
                                            name="telefono"
                                            label="Teléfono"
                                            type="tel"
                                            :value="old('telefono', $usuario->telefono)"
                                            :error="$errors->first('telefono')"
                                            optional
                                            maxlength="20"
                                            autocomplete="tel"
                                        />
                                        <x-ui.text-input
                                            name="correo"
                                            label="Correo"
                                            type="email"
                                            :value="old('correo', $usuario->correo)"
                                            :error="$errors->first('correo')"
                                            optional
                                            maxlength="100"
                                            autocomplete="email"
                                        />

                                        <div class="stack" data-direction="horizontal" data-gap="2" data-h-align="start" data-v-align="center">
                                            <x-ui.button type="submit" variant="primary">Guardar</x-ui.button>
                                            <x-ui.button href="{{ route('personal.index') }}" variant="secondary">Cancelar</x-ui.button>
                                        </div>
                                    </x-ui.form-layout>
                                </form>
                            </x-ui.grid>
    </x-ui.settings-page>
@endsection
