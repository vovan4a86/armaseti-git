<footer class="footer">
    <div class="footer__top">
        <div class="footer__container container">
            <div class="footer__body">
                @if(count($footer_menu))
                    <nav class="footer__menu">
                        <ul class="footer__menu-list list-reset">
                            @foreach($footer_menu as $item)
                                <li class="footer__menu-item">
                                    <a class="footer__menu-link" href="{{ $item->url }}">{{ $item->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif
                <div class="footer__data">
                    <div class="footer__col">
                        <div class="b-subscribe">
                            <div class="b-subscribe__icon lazy" data-bg="/static/images/common/ico_bell.svg"></div>
                            <div class="b-subscribe__body">
                                <div class="b-subscribe__title">Акции и распродажи</div>
                                <div class="b-subscribe__data">
                                    <button class="b-subscribe__link btn-reset" type="button"
                                            data-popup="data-popup" data-src="#subscribe" aria-label="Подписаться">
                                        <span class="b-subscribe__link-label">Подпишитесь</span>
                                        <span class="b-subscribe__link-icon iconify" data-icon="uil:arrow-right" data-width="18"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($address = Settings::get('footer_address'))
                        <div class="footer__col">
                            <div class="footer__info">
                                <svg class="svg-sprite-icon icon-pin" width="1em" height="1em">
                                    <use xlink:href="/static/images/sprite/symbol/sprite.svg#pin"></use>
                                </svg>
                                <span>{{ $address }}</span>
                            </div>
                        </div>
                    @endif
                    @if($email = Settings::get('footer_email'))
                        <div class="footer__col">
                            <a class="footer__info" href="mailto:{{ $email }}">
                                <svg class="svg-sprite-icon icon-email" width="1em" height="1em">
                                    <use xlink:href="/static/images/sprite/symbol/sprite.svg#email"></use>
                                </svg>
                                <span>{{ $email }}</span>
                            </a>
                        </div>
                    @endif
                    <div class="footer__col">
                        @if($phone = Settings::get('footer_phone'))
                            <a class="footer__phone" href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">
                                <svg class="svg-sprite-icon icon-phone" width="1em" height="1em">
                                    <use xlink:href="/static/images/sprite/symbol/sprite.svg#phone"></use>
                                </svg>
                                <span>{{ $phone }}</span>
                            </a>
                        @endif
                        <div class="footer__callback">
                            <button class="btn btn--accent btn--wide btn-reset" type="button"
                                    data-popup data-src="#callback" aria-label="Перезвоните мне">
                                <span class="btn__label">Перезвоните мне</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer__bottom">
        <div class="footer__container container">
            <div class="footer__copy">
                <img class="footer__logo" src="/static/images/common/logo--old.png" width="153" height="32" alt="armaseti" loading="lazy">
                <div class="footer__text">© 2009–{{ date('Y') }}. {{ Settings::get('footer_copy') }}</div>
            </div>
        </div>
    </div>
</footer>