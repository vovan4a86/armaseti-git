<!-- homepage && 'header--home'-->
<!-- innerPage && 'header--inner'-->
<header class="header {{ Route::is('main') ? 'header--home' : 'header--inner' }}">
    <div class="header__container container" x-data="{ dialogIsOpen: false }">
        <div class="header__grid">
            <button class="header__burger btn-reset" type="button" aria-label="Открыть меню"
                    @click="menuOverlayIsOpen = !menuOverlayIsOpen">
                <span class="iconify" data-icon="charm:menu-hamburger" data-width="40"></span>
            </button>
            <div class="header__logo">
                <div class="header__logo-desktop">
                    @if(Route::is('main'))
                        <div class="logo lazy" data-bg="/static/images/common/logo.svg"></div>
                    @else
                        <a href="{{ route('main') }}" class="logo lazy" data-bg="/static/images/common/logo.svg"></a>
                    @endif
                </div>
                <div class="header__logo-mobile">
                    <!--homepage ? "logo--white.svg" : "logo.svg"-->
                    @if(Route::is('main'))
                        <div class="logo logo--mobile lazy"
                             data-bg="/static/images/common/{{ Route::is('main') ? 'logo--white.svg' : 'logo.svg'}}"></div>
                    @else
                        <a href="{{ route('main') }}" class="logo logo--mobile lazy"
                           data-bg="/static/images/common/{{ Route::is('main') ? 'logo--white.svg' : 'logo.svg'}}"></a>
                    @endif
                </div>
            </div>
            <div class="header__body">
                <div class="header__top">
                    <div class="header__cities">
                        <div class="cities">
                            <a class="cities__current" href="{{ route('ajax.show-popup-cities') }}" data-cities
                               data-type="ajax"
                               data-home="{{ route('main') }}"
                               data-current="{{ request()->path() }}"
                               title="Изменить город">{{ isset($current_city) && $current_city ? $current_city->name : 'Екатеринбург' }}
                                <span class="cities__drop iconify" data-icon="ph:caret-down"></span>
                            </a>
                        </div>
                    </div>
                    @if ($phone = Settings::get('footer_phone'))
                        <div class="header__phone">
                            <a class="phone-link" href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">
                                {{ $phone }}</a>
                        </div>
                    @endif
                    <div class="header__callback">
                        <button class="btn btn--white btn-reset" type="button" aria-label="Заказать звонок" data-popup
                                data-src="#callback">
                            <span>Заказать звонок</span>
                        </button>
                    </div>
                </div>
                @if(count($header_menu))
                    <div class="header__bottom">
                        <div class="header__nav">
                            <nav class="nav" itemscope itemtype="https://schema.org/SiteNavigationElement"
                                 aria-label="Меню">
                                <ul class="nav__list list-reset" itemprop="about" itemscope
                                    itemtype="https://schema.org/ItemList">
                                    @foreach($header_menu as $item)
                                        <li class="nav__item" itemprop="itemListElement" itemscope
                                            itemtype="https://schema.org/ItemList">
                                            <a class="nav__link" href="{{ $item->url }}" itemprop="url" data-link>
                                                {{ $item->name }}
                                            </a>
                                            <meta itemprop="name" content="{{ $item->name }}">
                                        </li>
                                    @endforeach
                                </ul>
                            </nav>
                        </div>
                    </div>
                @endif
            </div>
            <button class="header__dots btn-reset" type="button" aria-label="Показать контакты"
                    @click="dialogIsOpen = true" x-cloak>
                <span class="iconify" data-icon="mdi:dots-vertical" data-width="30"></span>
            </button>
            <div class="header__dialog" x-show="dialogIsOpen" @click.away="dialogIsOpen = false"
                 x-transition.duration.500ms :class="dialogIsOpen &amp;&amp; 'is-active'">
                <div class="h-dialog">
                    @if($phone = Settings::get('footer_phone'))
                        <a class="h-dialog__phone"
                           href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">{{ $phone }}</a>
                    @endif
                    @if($email = Settings::get('footer_email'))
                        <a class="h-dialog__email" href="mailto:{{ $email }}">{{ $email }}</a>
                    @endif
                    <button class="h-dialog__location btn-reset" type="button" aria-label="Изменить город">
                        <span class="h-dialog__location-icon iconify" data-icon="material-symbols:location-on"></span>
                        <span class="h-dialog__location-label">Екатеринбург</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="header__overlay" x-show="menuOverlayIsOpen" @click.away="menuOverlayIsOpen = false"
         x-transition.duration.500ms :class="menuOverlayIsOpen &amp;&amp; 'is-active'" x-cloak>
        <div class="h-overlay">
            <div class="h-overlay__container">
                <button class="h-overlay__close btn-reset" type="button" aria-label="Закрыть меню"
                        @click="menuOverlayIsOpen = false">
                    <span class="iconify" data-icon="solar:close-square-bold-duotone" data-width="40"></span>
                </button>
                <div class="h-overlay__logo">
                    <a href="{{ route('main') }}" title="Luxkraft">
                        <span class="logo logo--mobile lazy" data-bg="/static/images/common/logo--white.svg"></span>
                    </a>
                </div>
                @if (count($mobile_menu))
                    <nav class="h-overlay__nav">
                        <ul class="h-overlay__nav-list list-reset">
                            @foreach($mobile_menu as $item)
                                <li class="h-overlay__nav-item">
                                    <a class="h-overlay__nav-link" href="{{ $item->url }}">{{ $item->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif
                @if($phone = Settings::get('footer_phone'))
                    <a class="h-overlay__phone" href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">{{ $phone }}</a>
                @endif
                @if($email = Settings::get('footer_email'))
                    <a class="h-overlay__email" href="mailto:{{ $email }}">{{ $email }}</a>
                @endif
                <a class="h-overlay__location" href="{{ route('ajax.show-popup-cities') }}" data-cities
                   data-home="{{ route('main') }}"
                   data-current="{{ request()->path() }}"
                   data-type="ajax" title="Изменить город">
                    <span class="h-overlay__location-icon iconify" data-icon="material-symbols:location-on"></span>
                    <span class="h-overlay__location-label">{{ isset($current_city) && $current_city ? $current_city->name : 'Екатеринбург' }}</span>
                </a>
            </div>
        </div>
    </div>
</header>
