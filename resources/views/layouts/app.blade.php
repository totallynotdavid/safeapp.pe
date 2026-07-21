<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'INFINITY') · SAFEAPP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <aside class="w-64 shrink-0 bg-gray-900 text-gray-200">
            <div class="flex h-14 items-center px-5 text-lg font-semibold tracking-wide text-white">
                INFINITY
            </div>
            @include('partials.sidebar')
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-14 items-center justify-between border-b border-gray-200 bg-white px-6">
                <h1 class="text-base font-semibold text-gray-700">@yield('title', 'Inicio')</h1>
                <div class="flex items-center gap-4 text-sm">
                    @include('partials.tienda-activa')
                    {{-- Name over cargo, which is what the old header's user menu
                         showed. Several screens scope on the role, so it is worth
                         having in view rather than a click away. --}}
                    <div class="leading-tight">
                        <div class="font-medium text-gray-700">{{ auth()->user()?->personal }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()?->rol?->nombre_rol }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 rounded-md bg-gray-100 px-3 py-1.5 font-medium text-gray-700 hover:bg-gray-200">
                            <x-heroicon-o-power class="size-4" />
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
