@if(count($catalog_menu))
    <aside class="layout__aside">
        <div class="layout__item">
            <!--nav.b-menu-->
            <nav class="b-menu">
                <ul class="b-menu__list">
                    @foreach($catalog_menu as $category)
                        <li class="b-menu__item">
                            <a class="b-menu__link" href="{{ $category->url }}" title="{{ $category->name }}"
                               data-link="data-link">
                            <span class="b-menu__link-icon">
                                @if ($category->menu_icon)
                                    <img src="{{ $category->iconSrc }}"
                                         width="24" height="28" alt="{{ $category->name }}"/>
                                @else
                                    <img src="{{ \Fanky\Admin\Models\Catalog::NO_CATALOG_ICON }}"
                                         width="24" height="24" alt="{{ $category->name }}"/>
                                @endif
                            </span>
                                <span class="b-menu__link-label">{{ $category->name }}</span>
                            </a>
                            @if(count($category->public_children))
                                <ul class="b-menu__sub faded">
                                    @foreach($category->public_children as $children)
                                        <li class="b-menu__sub-item">
                                            <a class="b-menu__sub-link"
                                               href="{{ $children->url }}">{{ $children->name }}</a>
                                            @if(count($children->public_children))
                                                <ul class="b-menu__sub-2 faded">
                                                    @foreach($children->public_children as $grand)
                                                        <li class="b-menu__sub-2-item">
                                                            <a class="b-menu__sub-2-link"
                                                               href="{{ $grand->url }}">{{ $grand->name }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
        @if($date = Settings::get('aside_update_catalog'))
            <div class="layout__item">
                <div class="b-updated">Каталог обновлён {{ $date }}</div>
            </div>
        @endif
        @include('blocks.aside_banner')
    </aside>
@endif
