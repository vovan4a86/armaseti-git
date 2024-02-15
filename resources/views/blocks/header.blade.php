<header class="header" :class="catalogIsOpen &amp;&amp; 'is-active'">
    <div class="header__top">
        <div class="header__top-row container">
            <div class="header__links">
                <div class="header__city">
                    <a class="b-city" href="{{ route('ajax.show-popup-cities') }}" data-cities="data-cities"
                       data-type="ajax" title="Выбрать город">
                        <svg class="svg-sprite-icon icon-pin" width="1em" height="1em">
                            <use xlink:href="/static/images/sprite/symbol/sprite.svg#pin"></use>
                        </svg>
                        <span class="b-city__label">{{ $current_city ? $current_city->name : 'Россия' }}</span>
                    </a>
                </div>
                @if(count($header_menu))
                    <div class="header__nav">
                        <nav class="h-nav">
                            <ul class="h-nav__list list-reset">
                                @foreach($header_menu as $item)
                                    <li class="h-nav__item">
                                        <a class="h-nav__link" href="{{ $item->url }}">{{ $item->name }}</a>
                                    </li>
                                @endforeach
                                @if($file = Settings::get('price_list'))
                                    <li class="h-nav__item">
                                        <a class="h-nav__link" href="{{ Settings::fileSrc($file) }}" download="armaseti-price">Прайс-лист</a>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                @endif
            </div>
            <div class="header__cont">
                @if($phone = Settings::get('header_phone'))
                    <div class="header__cont-col">
                        <a class="h-link" href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" title="{{ $phone }}">
                            <svg class="svg-sprite-icon icon-phone" width="1em" height="1em">
                                <use xlink:href="/static/images/sprite/symbol/sprite.svg#phone"></use>
                            </svg>
                            <span class="h-link__label">{{ $phone }}</span>
                        </a>
                    </div>
                @endif
                @if($email = Settings::get('header_email'))
                    <div class="header__cont-col">
                        <a class="h-link" href="mailto:{{ $email }}" title="{{ $email }}">
                            <svg class="svg-sprite-icon icon-email" width="1em" height="1em">
                                <use xlink:href="/static/images/sprite/symbol/sprite.svg#email"></use>
                            </svg>
                            <span class="h-link__label">{{ $email }}</span>
                        </a>
                    </div>
                @endif
                <div class="header__cont-col">
                    <button class="h-req btn-reset" type="button" aria-label="Отправить заявку" data-popup="data-popup"
                            data-src="#request">
                        <span>Отправить заявку</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="header__body">
        <div class="header__body-row container">
            <div class="header__info">
                @if(Route::is('main'))
                    <div class="header__logo">
                        <img class="logo" src="/static/images/common/logo.svg" width="60" height="36" alt="Армасети">
                    </div>
                @else
                    <a href="{{ route('main') }}" class="header__logo">
                        <img class="logo" src="/static/images/common/logo.svg" width="60" height="36" alt="Армасети">
                    </a>
                @endif
                <div class="header__label">Доставка от 3-х дней</div>
            </div>
            @if(count($header_features))
                <div class="header__features">
                    @foreach($header_features as $feat_item)
                        <div class="header__feat">
                            <div class="b-feat">
                                <span class="b-feat__icon"><img src="{{ Settings::fileSrc($feat_item['img']) }}" alt="feature {{ $loop->iteration }}"></span>
                                <span class="b-feat__label">{{ $feat_item['text'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="header__actions">
                @include('blocks.header_favorites')
                @include('blocks.header_compare')
                @include('blocks.header_cart')
            </div>
        </div>
    </div>
    <div class="header__bottom">
        <div class="header__bottom-row container">
            <div class="header__btn header__btn--desktop">
                <button class="h-btn btn-reset" type="button" @click="catalogIsOpen = !catalogIsOpen"
                        :class="catalogIsOpen &amp;&amp; 'is-active'" aria-label="Открыть каталог">
							<span class="h-btn__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="8"
                                                           fill="none"><g fill="currentColor"><path
                                                d="M0 5.5h24v2H0zM0 .5h24v2H0z"/></g></svg>
							</span>
                    <span class="h-btn__label">Каталог</span>
                </button>
            </div>
            <div class="header__btn header__btn--mobile">
                <button class="h-btn btn-reset" type="button" @click="mobNavIsOpen = !mobNavIsOpen"
                        :class="mobNavIsOpen &amp;&amp; 'is-active'" aria-label="Открыть меню">
							<span class="h-btn__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="8"
                                                           fill="none"><g fill="currentColor"><path
                                                d="M0 5.5h24v2H0zM0 .5h24v2H0z"/></g></svg>
							</span>
                </button>
            </div>
            <form class="header__search" action="{{ route('search') }}">
                <label class="h-search">
                    <input class="h-search__input" type="search" name="s" placeholder="Поиск по каталогу"
                           required="required"/>
                    <button class="h-search__btn btn-reset" aria-label="Найти">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none">
                            <path fill="#EA4D45" fill-rule="evenodd"
                                  d="M9.625 1.833a7.792 7.792 0 0 1 6.12 12.615l5.07 5.07-1.297 1.297-5.07-5.07A7.792 7.792 0 1 1 9.625 1.833Zm0 1.834a5.958 5.958 0 1 0 0 11.916 5.958 5.958 0 0 0 0-11.916Z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </button>
                </label>
            </form>
        </div>
    </div>
    @if(count($catalog_menu))
        <div class="header__menu faded" x-show="catalogIsOpen" :class="catalogIsOpen &amp;&amp; 'is-active'"
             @click.away="catalogIsOpen = false">
            <!--.b-overlay(x-data="{ openView: 1 }")-->
            <div class="b-overlay" x-data="{ openView: 1 }">
                <div class="b-overlay__container container">
                    <div class="b-overlay__grid">
                        <div class="b-overlay__views">
                            <ul class="b-overlay__list list-reset">
                                @foreach($catalog_menu as $category)
                                    <li class="b-overlay__list-item" @mouseover="openView = {{ $loop->iteration }}"
                                        :class="openView == {{ $loop->iteration }} &amp;&amp; 'is-active'">
                                        <a class="b-overlay__list-link"
                                           href="{{ $category->url }}">{{ $category->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="b-overlay__body">
                            @foreach($catalog_menu as $category)
                                <ul class="b-overlay__menu list-reset faded"
                                    x-show="openView == {{ $loop->iteration }}">
                                    @foreach($category->public_children as $children)
                                        <li class="b-overlay__menu-item">
                                            <a class="b-overlay__menu-link"
                                               href="{{ $children->url }}">{{ $children->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</header>
<div class="backdrop" :class="catalogIsOpen &amp;&amp; 'is-active'" @click="catalogIsOpen = !catalogIsOpen"></div>
