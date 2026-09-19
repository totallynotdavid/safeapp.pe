@extends('layouts.settings')

@section('title', 'Crear rol')
@section('page', 'true')

@section('content')
    <x-ui.settings-page title="Crear rol" description="Define el nombre y la plantilla de permisos." max-width="960px">
                            <x-ui.grid :min-width="320" gap="10">
                                <x-ui.stack direction="vertical" gap="1">
                                    <x-ui.heading level="3">Información del rol</x-ui.heading>
                                    <x-ui.text type="supporting" color="secondary">Define el nombre y las pantallas que podrá consultar este rol.</x-ui.text>
                                </x-ui.stack>

                                <form method="POST" action="{{ route('roles.store') }}">
                                    @csrf
                                    <x-ui.form-layout>
                                        <x-ui.text-input
                                            name="nombre_rol"
                                            label="Nombre del rol"
                                            :value="old('nombre_rol')"
                                            :error="$errors->first('nombre_rol')"
                                            required
                                            autofocus
                                            maxlength="50"
                                        />

                                        <x-ui.stack direction="vertical" gap="1">
                                            <x-ui.heading level="3">Permisos</x-ui.heading>
                                            <x-ui.text type="supporting" color="secondary">Selecciona las pantallas visibles para este rol.</x-ui.text>
                                        </x-ui.stack>
                                        @include('roles.permisos')

                                        <div class="stack" data-direction="horizontal" data-gap="2" data-h-align="start" data-v-align="center">
                                            <x-ui.button type="submit" variant="primary">Crear</x-ui.button>
                                            <x-ui.button href="{{ route('roles.index') }}" variant="secondary">Cancelar</x-ui.button>
                                        </div>
                                    </x-ui.form-layout>
                                </form>
                            </x-ui.grid>
    </x-ui.settings-page>
@endsection
