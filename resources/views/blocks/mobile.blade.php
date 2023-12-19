<div class="mob-nav" :class="mobNavIsOpen &amp;&amp; 'is-active'">
    <div class="mob-nav__container container">
        <div class="mob-nav__head">
            <div class="mob-nav__logo">
                <img src="/static/images/common/logo--old.png" width="153" height="32" alt="logo" loading="lazy">
            </div>
            <button class="mob-nav__close btn-reset" type="button" @click="mobNavIsOpen = !mobNavIsOpen" aria-label="Закрыть меню">
                <span class="mob-nav__close-icon iconify" data-icon="lets-icons:close-round-duotone" data-width="40" style="color: white;"></span>
            </button>
        </div>
        <div class="mob-nav__nav">
            <ul class="mob-nav__menu">
                @if($catalog_menu)
                    <li class="mob-nav__menu-item">
                        <a class="mob-nav__menu-link" href="{{ route('catalog') }}" data-sublink>
                            <span class="mob-nav__menu-label">Каталог</span>
                            <span class="mob-nav__menu-icon iconify" data-icon="basil:caret-down-outline" data-sublink-trigger data-width="30"></span>
                        </a>
                        <ul class="mob-nav__submenu" data-submenu>
                            @foreach($catalog_menu as $category)
                                <li class="mob-nav__submenu-item">
                                    <a class="mob-nav__submenu-link" href="{{ $category->url }}">{{ $category->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif
                @if(count($mobile_menu))
                    @foreach($mobile_menu as $item)
                        <li class="mob-nav__menu-item">
                            <a class="mob-nav__menu-link" href="{{ $item->url }}" title="{{ $item->name }}">
                                <span class="mob-nav__menu-label">{{ $item->name }}</span>
                            </a>
                        </li>
                    @endforeach
                @endif
                <li class="mob-nav__menu-item">
                    <a class="mob-nav__menu-link" href="javascript:void(0)" title="Прайс-лист">
                        <span class="mob-nav__menu-label">Прайс-лист</span>
                    </a>
                </li>
            </ul>
        </div>
        @if($phone = Settings::get('header_phone'))
            <div class="mob-nav__item">
                <a class="h-link h-link--wide" href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}"
                   title="{{ $phone }}">
                    <svg class="svg-sprite-icon icon-phone" width="1em" height="1em">
                        <use xlink:href="/static/images/sprite/symbol/sprite.svg#phone"></use>
                    </svg>
                    <span class="h-link__label">{{ $phone }}</span>
                </a>
            </div>
        @endif
        @if($email = Settings::get('header_email'))
            <div class="mob-nav__item">
                <a class="h-link" href="mailto:{{ $email }}" title="{{ $email }}">
                    <svg class="svg-sprite-icon icon-email" width="1em" height="1em">
                        <use xlink:href="/static/images/sprite/symbol/sprite.svg#email"></use>
                    </svg>
                    <span class="h-link__label">{{ $email }}</span>
                </a>
            </div>
        @endif
        <div class="mob-nav__item">
            <button class="b-city btn-reset b-city--white" type="button" data-popup="data-popup" data-src="#change-city" aria-label="Выбрать город">
                <svg class="svg-sprite-icon icon-pin" width="1em" height="1em">
                    <use xlink:href="/static/images/sprite/symbol/sprite.svg#pin"></use>
                </svg>
                <span class="b-city__label">Москва</span>
            </button>
        </div>
    </div>
</div>
<div class="mob-nav-backdrop" :class="mobNavIsOpen &amp;&amp; 'is-active'" @click="mobNavIsOpen = !mobNavIsOpen"></div>