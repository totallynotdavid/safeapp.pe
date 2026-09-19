<div class="side-nav__resizable-container"
     data-side-nav-resizable
     data-default-width="260"
     data-min-width="220"
     data-max-width="320">
    <nav class="side-nav" role="navigation" aria-label="Navegación principal" data-side-nav>
        <div class="side-nav__sticky-top">
            <a href="{{ route('home') }}" class="side-nav-heading" data-collapse-tooltip="Infinity">
                <x-ui.nav-icon class="side-nav-heading__icon" aria-hidden="true">
                    <x-ui.icon name="cube" size="sm" />
                </x-ui.nav-icon>
                <span class="side-nav-heading__text">
                    <span class="side-nav-heading__heading">Infinity</span>
                </span>
            </a>
            <div class="side-nav__top-content">
                @include('partials.tienda-activa', ['idPrefix' => 'operational-'])
            </div>
        </div>

        <div class="side-nav__scroll">
            @include('partials.sidebar-content', ['idPrefix' => 'primary-nav'])
        </div>

        <div class="side-nav__sticky-bottom">
            @php
                $accountName = str(auth()->user()?->personal)->lower()->title();
                $accountChildrenId = 'account-navigation-'.auth()->id();
                $accountSectionTitleId = 'account-section-'.auth()->id().'-title';
            @endphp
            <div class="side-nav__footer-content">
                <div class="side-nav-section" role="group" aria-labelledby="{{ $accountSectionTitleId }}">
                    <div class="side-nav-section__header" data-visually-hidden="true">
                        <span class="side-nav-section__title-container">
                            <span id="{{ $accountSectionTitleId }}" class="side-nav-section__title">Cuenta</span>
                        </span>
                    </div>
                    <div class="side-nav-section__items">
                        <div class="side-nav-item-tree">
                            <button type="button"
                                    class="side-nav-item"
                                    data-size="md"
                                    data-side-nav-toggle
                                    data-collapse-tooltip="{{ $accountName }}"
                                    aria-label="Cuenta de {{ $accountName }}"
                                    aria-controls="{{ $accountChildrenId }}"
                                    aria-expanded="false">
                                <span class="side-nav-item__icon" aria-hidden="true"><x-ui.avatar :name="$accountName" size="xsmall" /></span>
                                <span class="side-nav-item__label">{{ $accountName }}</span>
                                <span class="side-nav-item__chevron" aria-hidden="true"><x-ui.icon name="chevron-down" size="sm" /></span>
                            </button>
                            <div id="{{ $accountChildrenId }}"
                                 class="side-nav-item__children"
                                 data-label="Cuenta"
                                 aria-hidden="true"
                                 inert>
                                <div class="side-nav-item__children-inner">
                                    @if ($settingsUrl)
                                        <a href="{{ $settingsUrl }}" class="side-nav-item" data-size="md">
                                            <span class="side-nav-item__icon" aria-hidden="true"><x-ui.icon name="cog-6-tooth" size="sm" /></span>
                                            <span class="side-nav-item__label">Configuración</span>
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('logout') }}" class="side-nav-account-logout">
                                        @csrf
                                        <button type="submit" class="side-nav-item" data-size="md">
                                            <span class="side-nav-item__icon" aria-hidden="true"><x-ui.icon name="power" size="sm" /></span>
                                            <span class="side-nav-item__label">Cerrar sesión</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="side-nav__footer-row">
                <button type="button"
                        class="button ghost md side-nav-collapse-button"
                        data-variant="ghost"
                        data-size="md"
                        data-icon-only="true"
                        data-side-nav-collapse
                        aria-label="Contraer barra lateral"
                        aria-expanded="true">
                    <span class="button__content" aria-hidden="true">
                        <span class="button__icon side-nav-collapse-button__chevron"><x-ui.icon name="chevron-left" size="sm" /></span>
                    </span>
                </button>
            </div>
        </div>
    </nav>

    <div class="resize-handle"
         data-side-nav-resize-handle
         role="separator"
         aria-orientation="vertical"
         aria-valuenow="260"
         aria-valuemin="220"
         aria-valuemax="320"
         aria-label="Cambiar el ancho de la barra lateral"
         tabindex="0">
        <span class="resize-handle__hit-area" aria-hidden="true"></span>
        <span class="resize-handle-pill" aria-hidden="true"></span>
    </div>
</div>
