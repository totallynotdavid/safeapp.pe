@extends('layouts.app')

@section('title', 'Cargar datos')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <h1 class="text-xl font-semibold text-gray-800">Cargar datos de clientes</h1>

        @if (session('status'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        {{-- The file is rejected as a whole, so these are the reasons it was
             rejected, not a list of rows that failed to load. --}}
        @if (session('errores'))
            <div class="space-y-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-medium">No se cargó nada. Corrija el archivo y vuelva a subirlo.</p>
                <ul class="list-disc space-y-0.5 pl-5">
                    @foreach (session('errores') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('clientes.importar.store') }}" enctype="multipart/form-data" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6">
            @csrf

            @if ($errors->any())
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label for="archivo" class="block text-sm font-medium text-gray-700">Archivo CSV</label>
                <input id="archivo" name="archivo" type="file" accept=".csv,.txt" required
                       class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-sm file:font-medium focus:border-gray-500 focus:outline-none">
            </div>

            <button type="submit" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Cargar
            </button>
        </form>

        <div class="space-y-3 rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-600">
            <h2 class="text-sm font-semibold text-gray-800">Formato del archivo</h2>
            <p>
                La primera fila son los nombres de columna. Se aceptan archivos separados por
                punto y coma o por coma.
            </p>
            <div class="overflow-x-auto">
                <code class="whitespace-nowrap text-xs text-gray-800">{{ implode(';', $columnas) }}</code>
            </div>
            <ul class="list-disc space-y-1 pl-5">
                <li><span class="font-medium text-gray-800">ruc</span>, <span class="font-medium text-gray-800">tipo_data</span> y <span class="font-medium text-gray-800">origen</span> son obligatorios. El resto puede ir vacío.</li>
                <li><span class="font-medium text-gray-800">tipo_data</span> decide qué cola trabaja al cliente: {{ implode(' o ', \App\Models\Dato::TIPOS_DATA) }}.</li>
                <li><span class="font-medium text-gray-800">origen</span> es la campaña de la que viene. Los agentes piden lotes por origen.</li>
                <li>Un RUC que ya existe se actualiza; no se duplica. La carga nunca cambia a qué agente pertenece un cliente ni si ya fue gestionado.</li>
                <li>Si alguna fila tiene un error no se carga nada, para que no quede una carga a medias.</li>
            </ul>
        </div>
    </div>
@endsection
