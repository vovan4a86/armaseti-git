<footer class="footer">
    <div class="footer__container container">
        <div class="footer__grid">
            <div class="footer__logo">
                @if(Route::is('main'))
                    <div class="logo lazy" data-bg="/static/images/common/logo--white.svg"></div>
                @else
                    <a class="logo lazy" href="{{ route('main') }}" data-bg="/static/images/common/logo--white.svg"></a>
                @endif
            </div>
            @if(count($footer_menu))
                <nav class="footer__nav">
                    <ul class="footer__nav-list list-reset">
                        @foreach($footer_menu as $item)
                            <li class="footer__nav-item">
                                <a class="footer__nav-link" href="{{ $item->url }}">{{ $item->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif
            <div class="footer__info">
                <div class="footer__contacts">
                    @if ($phone = Settings::get('footer_phone'))
                        <div class="footer__key">Телефон:</div>
                        <a class="footer__value" href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">
                            {{ $phone }}
                        </a>
                    @endif
                    <div class="footer__socials">
                        @if ($vk = Settings::get('soc_vk'))
                            <a class="footer__icon" href="{{ $vk }}" title="Люкскрафт ВКонтакте"
                               target="_blank">
                                <span class="iconify" data-icon="ion:logo-vk"></span>
                            </a>
                        @endif
                        @if ($yt = Settings::get('soc_yt'))
                            <a class="footer__icon" href="{{ $yt }}" title="Люкскрафт Youtube" target="_blank">
                                <span class="iconify" data-icon="ant-design:youtube-filled"></span>
                            </a>
                        @endif
                    </div>
                </div>
                @if ($address = Settings::get('footer_address'))
                    <div class="footer__place">
                        <div class="footer__key">Адрес производства:</div>
                        <div class="footer__value">{{ $address }}</div>
                    </div>
                @endif
            </div>
        </div>
        <div class="footer__bottom">
            <a class="footer__policy" href="{{ route('policy') }}" target="_blank">Политика обработки персональных
                данных</a>
        </div>
    </div>
</footer>
