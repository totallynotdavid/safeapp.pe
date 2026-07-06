<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar · SAFEAPP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100">
    <div class="w-full max-w-sm rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <h1 class="text-center text-xl font-semibold tracking-wide text-gray-800">INFINITY</h1>
        <p class="mt-1 text-center text-sm text-gray-500">Sistema Llamadas</p>

        @if ($errors->any())
            <div class="mt-6 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="usuario" class="block text-sm font-medium text-gray-700">Usuario</label>
                <input id="usuario" name="usuario" type="text" required autofocus value="{{ old('usuario') }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
            </div>
            <div>
                <label for="clave" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input id="clave" name="clave" type="password" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
            </div>
            <button type="submit"
                class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                Ingresar
            </button>
        </form>
    </div>
</body>
</html>
