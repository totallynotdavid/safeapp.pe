<nav class="px-3 py-2 text-sm">
    @foreach ($sidebarSections as $section)
        <div class="mt-4 px-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
            {{ $section['descripcion'] }}
        </div>
        <ul class="mt-1 space-y-0.5">
            @foreach ($section['children'] as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="block rounded-md px-3 py-2 text-gray-300 hover:bg-gray-800 hover:text-white">
                        {{ $item['descripcion'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endforeach
</nav>
