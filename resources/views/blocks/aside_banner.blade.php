@if($aside_banner = Settings::get('aside_banner'))
    <div class="layout__item">
        <div class="aside-action lazy" data-bg="/static/images/common/aside-action-bg.jpg">
            <div class="aside-action__title">{{ $aside_banner['title'] }}</div>
            <div class="aside-action__text">{{ $aside_banner['text'] }}</div>
            <div class="aside-action__row">
                <a class="h-link" href="tel:{{ preg_replace('/[^\d+]/', '', $aside_banner['phone']) }}" title="{{ $aside_banner['phone'] }}">
                    <svg class="svg-sprite-icon icon-phone" width="1em" height="1em">
                        <use xlink:href="/static/images/sprite/symbol/sprite.svg#phone"></use>
                    </svg>
                    <span class="h-link__label">{{ $aside_banner['phone'] }}</span>
                </a>
            </div>
            <div class="aside-action__row">
                <button class="btn btn--accent btn-reset" type="button" data-popup="data-popup"
                        data-src="#request"
                        aria-label="Оставить заявку">
                    <span class="btn__label">Оставить заявку</span>
                </button>
            </div>
        </div>
    </div>
@endif
