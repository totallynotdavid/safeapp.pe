@extends('layouts.settings')
@section('title', 'Notificaciones')
@section('content')
    {{-- Privacy-panel hierarchy from settings-sidebar/page.tsx, applied to real delivery preferences. --}}
    <x-ui.stack direction="vertical" gap="6">
        <x-ui.heading level="2" class="settings-page__heading">Notificaciones</x-ui.heading>

        <x-ui.stack direction="vertical" gap="8">
            <x-ui.stack direction="vertical" gap="0">
                <x-ui.heading level="3">Por correo</x-ui.heading>
                <x-ui.divider />
                <form method="POST" action="{{ route('configuracion.notificaciones.update') }}" data-preference-form>
                    @csrf @method('PUT')
                    <x-ui.stack direction="vertical" gap="4" padding-block="4">
                        <x-ui.switch name="correo_cuenta" label="Cambios en mi cuenta" description="Actualizaciones de tu información personal." :checked="$preferences->correo_cuenta" label-position="start" label-spacing="spread" />
                        <x-ui.switch name="correo_seguridad" label="Cambios de seguridad" description="Avisos cuando se actualice tu contraseña." :checked="$preferences->correo_seguridad" label-position="start" label-spacing="spread" />
                    </x-ui.stack>
                </form>
                <x-ui.divider />
            </x-ui.stack>

            @if (! $usuario->correo_verificado_en)
                <x-ui.card variant="muted">
                    <x-ui.stack direction="horizontal" gap="4" v-align="start">
                        <x-ui.center class="settings-info-card__icon">
                            <x-ui.icon name="information-circle" />
                        </x-ui.center>
                        <x-ui.stack direction="vertical" gap="1">
                            <x-ui.text type="body" weight="bold">Correo pendiente de verificación</x-ui.text>
                            <x-ui.text type="body" color="secondary">Tus preferencias se guardan ahora y se aplicarán cuando verifiques un correo en Información personal.</x-ui.text>
                        </x-ui.stack>
                    </x-ui.stack>
                </x-ui.card>
            @endif

            <x-ui.stack direction="vertical" gap="0">
                <x-ui.heading level="3">Actividad reciente</x-ui.heading>
                <x-ui.divider />
                @forelse ($notifications as $notification)
                    <x-ui.stack direction="horizontal" gap="3" v-align="start" padding-block="4">
                        <x-ui.icon name="bell" />
                        <x-ui.stack direction="vertical" gap="0">
                            <x-ui.text type="body" weight="semibold">{{ $notification->titulo }}</x-ui.text>
                            <x-ui.text type="supporting" color="secondary">{{ $notification->descripcion }} · {{ $notification->created_at->format('d/m/Y H:i') }}</x-ui.text>
                        </x-ui.stack>
                    </x-ui.stack>
                @empty
                    <x-ui.stack direction="vertical" padding-block="4">
                        <x-ui.text type="body" color="secondary">Los cambios de cuenta y seguridad aparecerán aquí.</x-ui.text>
                    </x-ui.stack>
                @endforelse
                <x-ui.divider />
            </x-ui.stack>
        </x-ui.stack>
    </x-ui.stack>
@endsection
