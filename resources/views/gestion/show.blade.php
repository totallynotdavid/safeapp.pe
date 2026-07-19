@extends('layouts.app')

@section('title', $cliente->razon_social)

@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4 rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">{{ $cliente->razon_social }}</h2>
            <p class="text-sm text-gray-500">RUC {{ $cliente->ruc }} · {{ $cliente->tipo_data }}</p>
        </div>

        <dl class="grid grid-cols-2 gap-4 px-6 py-4 text-sm md:grid-cols-4">
            <div>
                <dt class="text-xs uppercase tracking-wider text-gray-500">Zonal</dt>
                <dd class="text-gray-800">{{ $cliente->zonal ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wider text-gray-500">Origen</dt>
                <dd class="text-gray-800">{{ $cliente->origen ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wider text-gray-500">Líneas {{ \App\Cockpit\Operadores::PROPIO }}</dt>
                <dd class="text-gray-800">{{ $cliente->lineasPropias() }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wider text-gray-500">Líneas competencia</dt>
                <dd class="text-gray-800">{{ $cliente->lineasCompetencia() }}</dd>
            </div>
        </dl>

        {{-- Read off the client already loaded above, so this costs no query. --}}
        <div class="flex flex-wrap gap-x-6 gap-y-1 border-t border-gray-100 px-6 py-3 text-sm text-gray-600">
            @foreach (\App\Cockpit\Operadores::TODOS as $operador)
                <span>
                    {{ $operador }}
                    <span class="font-semibold text-gray-800">{{ $cliente->{\App\Cockpit\Operadores::columna($operador)} }}</span>
                </span>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('gestion.store', $cliente) }}" class="rounded-lg border border-gray-200 bg-white">
        @csrf

        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="font-semibold text-gray-800">Registrar gestión</h3>
        </div>

        <div class="grid grid-cols-1 gap-4 px-6 py-4 md:grid-cols-2">
            <div>
                <label for="telefono" class="mb-1 block text-sm font-medium text-gray-700">Teléfono</label>
                <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}" required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="id_respuesta" class="mb-1 block text-sm font-medium text-gray-700">Respuesta</label>
                <select id="id_respuesta" name="id_respuesta" required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Elija una respuesta</option>
                    {{-- The channel is a property of the chosen respuesta, so it
                         is shown as grouping rather than asked for. --}}
                    @foreach ($respuestas as $canal => $opciones)
                        <optgroup label="{{ $canal }}">
                            @foreach ($opciones as $respuesta)
                                <option value="{{ $respuesta->id_respuesta }}" @selected((int) old('id_respuesta') === $respuesta->id_respuesta)>
                                    {{ $respuesta->descripcion }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="nombre_contacto" class="mb-1 block text-sm font-medium text-gray-700">Nombre de contacto</label>
                <input type="text" id="nombre_contacto" name="nombre_contacto" value="{{ old('nombre_contacto') }}"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="correo" class="mb-1 block text-sm font-medium text-gray-700">Correo</label>
                <input type="email" id="correo" name="correo" value="{{ old('correo') }}"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div class="border-t border-gray-200 px-6 py-4">
            <p class="mb-3 text-xs uppercase tracking-wider text-gray-500">Líneas por operador</p>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                {{-- One input per carrier, so a change to the carrier list does
                     not need a template edit. --}}
                @foreach (\App\Cockpit\Operadores::TODOS as $operador)
                    @php($columna = \App\Cockpit\Operadores::columna($operador))
                    <div>
                        <label for="{{ $columna }}" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ $operador }}
                            @if ($operador === \App\Cockpit\Operadores::PROPIO)
                                <span class="text-xs font-normal text-gray-500">(nuestras)</span>
                            @endif
                        </label>
                        <input type="number" min="0" id="{{ $columna }}" name="{{ $columna }}"
                            value="{{ old($columna, $cliente->{$columna}) }}"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-gray-200 px-6 py-4">
            <label for="comentario" class="mb-1 block text-sm font-medium text-gray-700">Comentario</label>
            <textarea id="comentario" name="comentario" rows="2"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">{{ old('comentario') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4">
            <a href="{{ route($rutaBase) }}"
                class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                Cancelar
            </a>
            <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                Registrar gestión
            </button>
        </div>
    </form>

    <div class="mt-4 rounded-lg border border-gray-200 bg-white">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h3 class="font-semibold text-gray-800">Historial de contactos</h3>
            <span class="text-sm text-gray-500">{{ $historial->count() }} registrados</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3">Agente</th>
                        <th class="px-6 py-3">Contacto</th>
                        <th class="px-6 py-3">Teléfono</th>
                        <th class="px-6 py-3">Respuesta</th>
                        <th class="px-6 py-3">Comentario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($historial as $llamada)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-3 text-gray-800">{{ $llamada->fecha?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $llamada->agente?->personal ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $llamada->nombre_contacto ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $llamada->telefono }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $llamada->respuesta?->descripcion ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $llamada->comentario ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                Este cliente aún no tiene contactos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="font-semibold text-gray-800">Teléfonos conocidos</h3>
            <p class="text-sm text-gray-500">Números usados al contactar a este cliente.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Teléfono</th>
                        <th class="px-6 py-3">Contactos</th>
                        <th class="px-6 py-3">Último uso</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($telefonos as $telefono)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-800">{{ $telefono->telefono }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $telefono->veces }}</td>
                            <td class="whitespace-nowrap px-6 py-3 text-gray-600">
                                {{ \Illuminate\Support\Carbon::parse($telefono->ultimo_contacto)->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                                Sin teléfonos registrados todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
