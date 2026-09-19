@foreach ($sidebarSections as $sectionIndex => $section)
    @php($titleId = ($idPrefix ?? 'side-nav').'-section-'.$sectionIndex.'-title')
    <div class="side-nav-section"
         role="group"
         aria-labelledby="{{ $titleId }}">
        <div class="side-nav-section__header" @if ($section['hidden']) data-visually-hidden="true" @endif>
            <span class="side-nav-section__title-container">
                <span id="{{ $titleId }}" class="side-nav-section__title">{{ $section['title'] }}</span>
            </span>
        </div>
        <div class="side-nav-section__items">
            @foreach ($section['items'] as $item)
                <a href="{{ $item['url'] }}"
                   class="side-nav-item"
                   data-size="md"
                   data-collapse-tooltip="{{ $item['label'] }}"
                   @if ($item['active']) data-selected="selected" aria-current="page" @endif>
                    <span class="side-nav-item__icon" aria-hidden="true">
                        <x-ui.icon :name="$item['icon']" size="sm" />
                    </span>
                    <span class="side-nav-item__label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endforeach
