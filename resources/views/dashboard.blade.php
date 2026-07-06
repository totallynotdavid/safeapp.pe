@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <div class="rounded-lg border border-gray-200 bg-white p-6">
        <h2 class="text-lg font-semibold text-gray-800">Bienvenido, {{ auth()->user()->personal }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            Rol: {{ auth()->user()->rol?->nombre_rol ?? 'Sin rol' }}
        </p>
    </div>
@endsection
