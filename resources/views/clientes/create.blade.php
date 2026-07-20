@extends('layouts.app')

@section('title', 'Agregar cliente')

@section('content')
    @php($field = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none')
    @php($label = 'block text-sm font-medium text-gray-700')

    <div class="mx-auto max-w-3xl space-y-6">
        <h1 class="text-xl font-semibold text-gray-800">Agregar cliente</h1>

        @if (session('error'))
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        @include('partials.buscar-cliente', ['ruta' => 'clientes.create', 'ruc' => $ruc])

        @if ($dato)
            {{-- The customer is already on a campaign list. Loading them again is
                 never the answer, so the only offer is to take the record when
                 nobody holds it. --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-semibold text-gray-800">{{ $cliente->razon_social }}</h2>
                <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3">
                    @include('partials.campo', ['label' => 'RUC', 'value' => $cliente->ruc])
                    @include('partials.campo', ['label' => 'Segmento', 'value' => $dato->tipo_data])
                    @include('partials.campo', ['label' => 'Origen', 'value' => $dato->origen])
                </dl>

                @if ($dato->asignacionActiva === null)
                    <form method="POST" action="{{ route('clientes.tomar', $dato) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            Tomar este cliente
                        </button>
                    </form>
                @else
                    <p class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-900">
                        Asignado a {{ $dato->asignacionActiva->agente?->personal ?? 'otro agente' }}. Solicite la reasignación a su supervisor.
                    </p>
                @endif
            </div>
        @elseif ($ruc)
            <form method="POST" action="{{ route('clientes.store') }}" class="space-y-6 rounded-lg border border-gray-200 bg-white p-6">
                @csrf
                <input type="hidden" name="ruc" value="{{ $ruc }}">

                @if ($errors->any())
                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p class="text-sm text-gray-600">
                    El RUC <span class="font-medium text-gray-800">{{ $ruc }}</span> no está registrado.
                </p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label for="razon_social" class="{{ $label }}">Razón social</label>
                        <input id="razon_social" name="razon_social" value="{{ old('razon_social') }}" required class="{{ $field }}">
                    </div>
                    <div>
                        {{-- Required, and only the segments that have a queue. A
                             client filed anywhere else can never be worked. --}}
                        <label for="tipo_data" class="{{ $label }}">Segmento</label>
                        <select id="tipo_data" name="tipo_data" required class="{{ $field }}">
                            <option value="">-- Elegir --</option>
                            @foreach (\App\Models\Dato::TIPOS_DATA as $segmento)
                                <option value="{{ $segmento }}" @selected(old('tipo_data') === $segmento)>{{ $segmento }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label for="zonal" class="{{ $label }}">Zonal (departamento SUNAT)</label>
                        <input id="zonal" name="zonal" value="{{ old('zonal') }}" class="{{ $field }}">
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700">Líneas por operador</h3>
                    <div class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @foreach (\App\Cockpit\Operadores::TODOS as $operador)
                            @php($columna = \App\Cockpit\Operadores::columna($operador))
                            <div>
                                <label for="{{ $columna }}" class="block text-xs font-medium uppercase tracking-wider text-gray-500">{{ $operador }}</label>
                                <input id="{{ $columna }}" name="{{ $columna }}" type="number" min="0" value="{{ old($columna, 0) }}" class="{{ $field }}">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        Registrar y gestionar
                    </button>
                    <a href="{{ route('clientes.create') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Cancelar</a>
                </div>
            </form>
        @endif
    </div>
@endsection
