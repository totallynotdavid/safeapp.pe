@extends('layouts.settings')
@section('title', 'Inicio de sesión y seguridad')
@section('content')
    {{-- Login & security panel, based on settings-sidebar/page.tsx. --}}
    <x-ui.stack direction="vertical" gap="6">
        <x-ui.heading level="2" class="settings-page__heading">Inicio de sesión y seguridad</x-ui.heading>

        <x-ui.stack direction="vertical" gap="8">
            <x-ui.stack direction="vertical" gap="0">
                <x-ui.heading level="3">Inicio de sesión</x-ui.heading>
                <x-ui.divider />
                <x-ui.settings-expandable-row
                    field="password"
                    label="Contraseña"
                    value="Protegida"
                    :action="route('configuracion.seguridad.update')"
                    :expanded="$errors->hasAny(['clave_actual', 'clave', 'clave_confirmation'])"
                    :include-field="false">
                    <x-ui.text-input name="clave_actual" label="Contraseña actual" type="password" value="" :error="$errors->first('clave_actual')" required autocomplete="current-password" />
                    <x-ui.text-input name="clave" label="Nueva contraseña" type="password" value="" description="Usa al menos 8 caracteres." :error="$errors->first('clave')" required autocomplete="new-password" />
                    <x-ui.text-input name="clave_confirmation" label="Confirmar nueva contraseña" type="password" value="" :error="$errors->first('clave_confirmation')" required autocomplete="new-password" />
                </x-ui.settings-expandable-row>
            </x-ui.stack>

            <x-ui.stack direction="vertical" gap="0">
                <x-ui.heading level="3">Cuenta</x-ui.heading>
                <x-ui.divider />
                <x-ui.settings-info-row label="Usuario de inicio de sesión" :value="$usuario->usuario" />
            </x-ui.stack>
        </x-ui.stack>
    </x-ui.stack>
@endsection
