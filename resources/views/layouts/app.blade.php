<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="neutral">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'INFINITY') · SAFEAPP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell" data-variant="elevated" data-height="fill">
        <a href="#app-shell-main" class="app-shell__skip">Saltar al contenido</a>

        <div class="layout" data-height="fill">
            <div class="layout__inner">
                <div class="app-shell-header layout-header">
                    <div class="layout-header__inner mobile-bar" role="navigation" aria-label="Navegación móvil">
                        <a href="{{ route('home') }}" class="side-nav-heading">
                            <x-ui.nav-icon class="side-nav-heading__icon" aria-hidden="true"><x-ui.icon name="cube" size="sm" /></x-ui.nav-icon>
                            <span class="side-nav-heading__text"><span class="side-nav-heading__heading">Infinity</span></span>
                        </a>
                        <button type="button"
                                class="button ghost md mobile-nav-toggle"
                                data-variant="ghost"
                                data-size="md"
                                data-mobile-nav-open
                                aria-label="Abrir navegación"
                                aria-haspopup="dialog"
                                aria-controls="mobile-navigation"
                                aria-expanded="false">
                            <span class="button__content" aria-hidden="true"><x-ui.icon name="bars-3" /></span>
                        </button>
                    </div>
                </div>

                <div class="layout__middle">
                    <div class="layout-panel app-shell-sidenav" data-variant="elevated" data-app-shell-sidenav data-side-nav-home>
                        @include('partials.sidebar')
                    </div>

                    <div class="layout__content-slot">
                        <main id="app-shell-main" class="app-shell-main">
                            @if (session('status') || session('error'))
                                <div class="toast-viewport" data-position="bottom-end" aria-label="Notificaciones">
                                    @foreach ([['type' => 'info', 'body' => session('status')], ['type' => 'error', 'body' => session('error')]] as $toast)
                                        @if ($toast['body'])
                                            <div class="toast-wrapper" data-toast-wrapper>
                                                <div class="toast-wrapper__inner">
                                                    <div class="toast" data-type="{{ $toast['type'] }}"
                                                        role="{{ $toast['type'] === 'error' ? 'alert' : 'status' }}"
                                                        aria-live="{{ $toast['type'] === 'error' ? 'assertive' : 'polite' }}" aria-atomic="true">
                                                        <div class="toast__inner">
                                                            <div class="toast__content">{{ $toast['body'] }}</div>
                                                            <div class="toast__end">
                                                                <button type="button" class="button ghost sm" data-variant="ghost" data-size="sm"
                                                                    data-icon-only="true" data-toast-dismiss aria-label="Cerrar notificación">
                                                                    <span class="button__content" aria-hidden="true"><span class="button__icon"><x-ui.icon name="x-mark" size="sm" /></span></span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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

        <dialog id="mobile-navigation"
                class="mobile-nav"
                data-side="start"
                data-mobile-nav
                aria-label="Navegación principal">
            <div class="mobile-nav__drawer" tabindex="-1" data-mobile-nav-drawer>
                <div class="mobile-nav__header">
                    <span class="side-nav-heading__heading">Infinity</span>
                    <button type="button"
                            class="button ghost md mobile-nav__close"
                            data-variant="ghost"
                            data-size="md"
                            data-mobile-nav-close
                            aria-label="Cerrar navegación">
                        <span class="button__content" aria-hidden="true"><x-ui.icon name="x-mark" /></span>
                    </button>
                </div>

                <div class="mobile-nav__content" data-mobile-nav-content></div>
            </div>
        </dialog>
    </div>
</body>
</html>
