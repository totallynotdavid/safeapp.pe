@extends('layouts.app')

@section('title', 'Asignar tiendas')

@section('content')
    <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6">
        <h2 class="mb-1 text-lg font-semibold text-gray-800">Asignar tiendas</h2>
        <p class="mb-4 text-sm text-gray-500">{{ $usuario->personal }} · {{ $usuario->usuario }}</p>

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('asignacion.update', $usuario) }}" class="space-y-4">
            @csrf
            @method('PUT')

            @forelse ($tiendas as $tienda)
                @php($asignada = in_array($tienda->id_tienda, old('tiendas', $asignadas), false))
                <div class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-2">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="tiendas[]" value="{{ $tienda->id_tienda }}" @checked($asignada)>
                        {{ $tienda->nombre }}
                    </label>
                    <label class="flex items-center gap-2 text-xs text-gray-500">
                        <input type="radio" name="activa" value="{{ $tienda->id_tienda }}" @checked((int) old('activa', $activa) === $tienda->id_tienda)>
                        Activa
                    </label>
                </div>
            @empty
                <p class="text-sm text-gray-500">No hay tiendas registradas.</p>
            @endforelse

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Guardar
                </button>
                <a href="{{ route('asignacion.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
