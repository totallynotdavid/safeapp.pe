@extends('layouts.auth')

@section('title', 'Ingresar')

@section('content')
    <x-ui.center as="main" axis="both" class="auth-page">
        <x-ui.stack gap="4" align="center" class="auth-column">
            <x-ui.stack gap="2" align="center">
                <x-ui.icon name="cube" size="lg" class="brand-icon" />
                <x-ui.text size="lg" weight="bold">INFINITY</x-ui.text>
            </x-ui.stack>

            <x-ui.card padding="8" width="full">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <x-ui.stack gap="4" align="stretch">
                        <x-ui.stack gap="1" align="center">
                            <x-ui.heading level="1">Accede a SAFEAPP</x-ui.heading>

                            <x-ui.text type="supporting" color="secondary" class="auth-intro">
                                Ingresa con tus credenciales corporativas.
                            </x-ui.text>
                        </x-ui.stack>

                        <x-ui.stack gap="2" align="stretch">
                            <x-ui.text-input
                                name="usuario"
                                label="Usuario"
                                autocomplete="username"
                                :value="old('usuario')"
                                :error="$errors->first('usuario')"
                                size="lg"
                                required
                                autofocus
                            />

                            <x-ui.text-input
                                name="clave"
                                label="Contraseña"
                                type="password"
                                autocomplete="current-password"
                                :error="$errors->first('clave')"
                                size="lg"
                                required
                            />
                        </x-ui.stack>

                        <x-ui.button type="submit" variant="primary" size="lg" width="full">
                            Ingresar
                        </x-ui.button>

                        <x-ui.divider label="¿Necesitas ayuda?" />

                        <x-ui.stack align="center" width="full">
                            <x-ui.text type="supporting" color="secondary" class="auth-support">
                                Si no tienes acceso o no recuerdas tus credenciales, comunícate con tu supervisor inmediato.
                            </x-ui.text>
                        </x-ui.stack>
                    </x-ui.stack>
                </form>
            </x-ui.card>

            <x-ui.stack align="center" width="full">
                <x-ui.text type="supporting" color="secondary" class="auth-support">
                    SAFEAPP es de uso exclusivo para personal autorizado.
                </x-ui.text>
            </x-ui.stack>
        </x-ui.stack>
    </x-ui.center>
@endsection
