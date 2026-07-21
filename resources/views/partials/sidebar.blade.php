{{-- Sections collapse, as they did before the rewrite: eight of them holding
     twenty leaves is too much to read as one flat list. <details> does it
     natively, so the menu keeps working with no JavaScript. --}}
<nav class="px-2 py-2 text-sm">
    @foreach ($sidebarSections as $section)
        <details class="group" @if ($section['abierta']) open @endif>
            <summary class="flex cursor-pointer items-center gap-3 rounded-md px-3 py-2 text-gray-300 hover:bg-gray-800 hover:text-white">
                <x-dynamic-component :component="$section['icono']" class="size-5 shrink-0" />
                <span class="flex-1 truncate">{{ $section['descripcion'] }}</span>
                <x-heroicon-o-chevron-right class="size-4 shrink-0 transition-transform group-open:rotate-90" />
            </summary>

            <ul class="mt-0.5 mb-1 space-y-0.5">
                @foreach ($section['children'] as $item)
                    <li>
                        <a href="{{ $item['url'] }}"
                           @if ($item['activa']) aria-current="page" @endif
                           class="flex items-center gap-3 rounded-md py-2 pr-3 pl-8 {{ $item['activa'] ? 'bg-gray-800 font-medium text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            <x-dynamic-component :component="$item['icono']" class="size-4 shrink-0" />
                            <span class="truncate">{{ $item['descripcion'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </details>
    @endforeach
</nav>
