{{-- Only shown to operators with somewhere to switch to. One tienda (or none)
     means there is no choice to make, so the control would be noise. --}}
@if ($tiendasAsignadas->count() > 1)
    <form method="POST" action="{{ route('tienda-activa.update') }}" class="flex items-center gap-2">
        @csrf
        @method('PUT')
        <label for="id_tienda" class="sr-only">Tienda activa</label>
        <select id="id_tienda" name="id_tienda" onchange="this.form.submit()"
                class="rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-gray-500 focus:outline-none">
            @foreach ($tiendasAsignadas as $asignacion)
                <option value="{{ $asignacion->id_tienda }}" @selected($asignacion->estado === 0)>
                    {{ $asignacion->tienda?->nombre ?? 'Tienda '.$asignacion->id_tienda }}
                </option>
            @endforeach
        </select>
        <noscript>
            <button type="submit" class="rounded-md bg-gray-100 px-2 py-1 text-sm font-medium text-gray-700 hover:bg-gray-200">Cambiar</button>
        </noscript>
    </form>
@endif
