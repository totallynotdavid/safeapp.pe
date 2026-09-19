<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="neutral">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Configuración') · SAFEAPP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="settings-layout page-layout layout" data-height="fill" data-content-width="1200" @hasSection('settings-index') data-settings-index="true" @endif>
        <div class="page-layout__inner">
            <div class="page-layout__middle">
                <aside class="layout-panel settings-layout__panel" role="navigation" aria-label="Configuración">
                    @include('partials.settings-nav')
                </aside>
                <div class="page-layout__content-slot">
                    <main id="settings-content" class="layout-content settings-layout__content" data-padding="4">
                        @unless ($__env->hasSection('settings-index'))
                            <div class="settings-mobile-toolbar" role="toolbar" aria-label="Volver a Configuración — @yield('title')">
                                <x-ui.button href="{{ route('configuracion.index') }}" variant="ghost" size="sm" :icon-only="true" aria-label="Volver a Configuración">
                                    <x-slot:icon><x-ui.icon name="arrow-left" size="sm" /></x-slot:icon>
                                </x-ui.button>
                                <x-ui.heading level="2">@yield('title')</x-ui.heading>
                            </div>
                        @endunless

                        @if (session('status') || session('error'))
                            <div class="toast-viewport" data-position="bottom-end" aria-label="Notificaciones">
                                @foreach ([['type' => 'info', 'body' => session('status')], ['type' => 'error', 'body' => session('error')]] as $toast)
                                    @if ($toast['body'])
                                        <div class="toast-wrapper" data-toast-wrapper><div class="toast-wrapper__inner">
                                            <div class="toast" data-type="{{ $toast['type'] }}" role="{{ $toast['type'] === 'error' ? 'alert' : 'status' }}" aria-live="{{ $toast['type'] === 'error' ? 'assertive' : 'polite' }}" aria-atomic="true">
                                                <div class="toast__inner"><div class="toast__content">{{ $toast['body'] }}</div><div class="toast__end">
                                                    <button type="button" class="button ghost sm" data-variant="ghost" data-size="sm" data-icon-only="true" data-toast-dismiss aria-label="Cerrar notificación">
                                                        <span class="button__content" aria-hidden="true"><span class="button__icon"><x-ui.icon name="x-mark" size="sm" /></span></span>
                                                    </button>
                                                </div></div>
                                            </div>
                                        </div></div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        @yield('content')
                    </main>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
