<div class="prod__tabs">
    <div class="prod-tabs tabs" data-tabs="data-tabs">
        <!--._nav.tabs__nav-->
        <div class="prod-tabs__nav tabs__nav">
            <div class="prod-tabs__link tabs__link is-active" data-open="Описание">Описание</div>
            <div class="prod-tabs__link tabs__link" data-open="Документация">Документация</div>
            @if(count($related))
                <div class="prod-tabs__link tabs__link" data-open="Аналоги">Аналоги</div>
            @endif
        </div>
        <!--._views.tabs__views-->
        <div class="prod-tabs__views tabs__views">
            <!--._view-->
            <div class="prod-tabs__view tabs__view is-active" data-view="Описание">
                <div class="prod-tabs__grid">
                    <div class="prod-tabs__data">
                        <div class="prod-tabs__text text-block">
                            <h2>О товаре</h2>
                            {!! $product->text !!}
                        </div>
                        @if(count($features))
                            <div class="prod-tabs__feat">
                                <div class="s-feat s-feat--grey">
                                    <div class="s-feat__subtitle">Преимущества продукции</div>
                                    <div class="s-feat__grid s-feat__grid--small">
                                        @foreach($features as $feat)
                                            <div class="s-feat__item">
                                                <div class="s-feat__icon lazy"
                                                     data-bg="{{ $feat->imageSrc() }}"></div>
                                                <div class="s-feat__title">{{ $feat->text }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="prod-tabs__info">
                            <div class="prod-tabs__info-title">Подробные характеристики</div>
                            <div class="prod-tabs__info-list">
                                <div class="data-list no-select">
                                    @foreach($product->chars as $char)
                                        <dl class="data-list__list">
                                            <dt class="data-list__term">{{ $char->name }}</dt>
                                            <dd class="data-list__desc">{{ $char->value }}</dd>
                                        </dl>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--                    <div class="prod-tabs__extra">--}}
                    {{--                        <div class="prod-tabs__video">--}}
                    {{--                            <a class="b-video tube lazy" data-bg="/static/images/common/cover.jpg"--}}
                    {{--                               href="https://rutube.ru/video/656aa8404aea6017d4c404ca9ddd4eb0/"--}}
                    {{--                               title="Задвижка стальная фланцевая 30с41нж">--}}
                    {{--                                <span class="b-video__subtitle">Обзор</span>--}}
                    {{--                                <span class="b-video__desc">Задвижка стальная фланцевая 30с41нж</span>--}}
                    {{--                                <img class="b-video__pic no-select" src="/static/images/common/vid.png"--}}
                    {{--                                     width="286" height="229" alt="" loading="lazy"/>--}}
                    {{--                                <img class="b-video__play lazy no-select"--}}
                    {{--                                     src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="--}}
                    {{--                                     data-src="/static/images/common/ico_play.svg" width="155"--}}
                    {{--                                     height="145" alt=""/>--}}
                    {{--                            </a>--}}
                    {{--                        </div>--}}
                    {{--                        <div class="prod-tabs__decor">--}}
                    {{--                            <img class="prod-tabs__decor-img no-select"--}}
                    {{--                                 src="/static/images/common/decor-1.jpg" width="428" height="320"--}}
                    {{--                                 alt=""/>--}}
                    {{--                            <img class="prod-tabs__decor-img no-select"--}}
                    {{--                                 src="/static/images/common/decor-2.jpg" width="428" height="320"--}}
                    {{--                                 alt=""/>--}}
                    {{--                            <img class="prod-tabs__decor-img no-select"--}}
                    {{--                                 src="/static/images/common/decor-3.jpg" width="428" height="320"--}}
                    {{--                                 alt=""/>--}}
                    {{--                        </div>--}}
                    {{--                    </div>--}}
                </div>
            </div>
            <!--._view-->
            <div class="prod-tabs__view tabs__view" data-view="Документация">
                <div class="b-docs">
                    @if (count($product->docs))
                        @foreach($product->docs as $doc)
                            <div class="b-docs__item">
                                <a class="b-docs__view" href="{{ $doc->fileSrc($product->catalog->slug) }}"
                                   data-fancybox="data-fancybox" data-caption="{{ $doc->name }}"
                                   data-type="pdf" title="Сертификат диллера">
                                    <span class="b-docs__title">{{ $doc->name }}</span>
                                </a>
                                <div class="b-docs__download">
                                    <a class="b-download" href="{{ $doc->fileSrc($product->catalog->slug) }}"
                                       download="{{ $doc->name }}">
                                    <span class="b-download__icon iconify"
                                          data-icon="material-symbols:download" data-width="20"></span>
                                        <span class="b-download__label">Скачать документ</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        @foreach($product->catalog->docs as $doc)
                            <div class="b-docs__item">
                                <a class="b-docs__view" href="{{ $doc->fileSrc($product->catalog->slug) }}"
                                   data-fancybox="data-fancybox" data-caption="{{ $doc->name }}"
                                   data-type="pdf" title="{{ $doc->name }}">
                                    <span class="b-docs__title">{{ $doc->name }}</span>
                                </a>
                                <div class="b-docs__download">
                                    <a class="b-download" href="{{ $doc->fileSrc($product->catalog->slug) }}"
                                       download="{{ $doc->name }}">
                                    <span class="b-download__icon iconify"
                                          data-icon="material-symbols:download" data-width="20"></span>
                                        <span class="b-download__label">Скачать документ</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <!--._view-->
            @if(count($related))
                <div class="prod-tabs__view tabs__view" data-view="Аналоги">
                    <div class="b-related">
                        <div class="b-related__grid">
                            @foreach($related as $item)
                                <div class="b-related__item">
                                    <form class="prod-card prod-card--wide" action="#">
                                        <input type="hidden" name="id"/>
                                        <input type="hidden" name="product"
                                               value="Фланцевый REON RSV16 вентиль запорный"/>
                                        <input type="hidden" name="count" value="1"/>
                                        <div class="prod-card__view">
                                            <div class="prod-card__utils">
                                                <div class="prod-card__utils-item">
                                                    <!-- .is-active — active color state-->
                                                    <button class="utils-btn btn-reset" type="button"
                                                            aria-label="Добавить к сравнению">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="17"
                                                             height="18" fill="none">
                                                            <path fill="currentColor"
                                                                  d="M0 2.436c0-.587.544-1.063 1.214-1.063.67 0 1.215.476 1.215 1.063v13.812c0 .587-.544 1.063-1.215 1.063-.67 0-1.214-.476-1.214-1.063V2.436ZM7.286 5.623c0-.587.543-1.062 1.214-1.062.67 0 1.214.475 1.214 1.062v10.625c0 .587-.543 1.063-1.214 1.063-.67 0-1.214-.476-1.214-1.063V5.623ZM14.571 1.373c0-.587.544-1.062 1.215-1.062.67 0 1.214.475 1.214 1.062v14.875c0 .587-.544 1.063-1.214 1.063-.67 0-1.215-.476-1.215-1.063V1.373Z"
                                                            />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div class="prod-card__utils-item">
                                                    <!-- .is-active — active color state-->
                                                    <button class="utils-btn btn-reset" type="button"
                                                            aria-label="Добавить в избранное">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="22"
                                                             height="19" fill="none">
                                                            <path fill="currentColor"
                                                                  d="M20.233 1.794a6.143 6.143 0 0 0-8.678 0L11 2.35l-.556-.556a6.142 6.142 0 0 0-8.676 0c-2.344 2.344-2.359 6.058-.036 8.641 2.12 2.355 8.37 7.442 8.634 7.658.18.146.397.217.612.217H11a.937.937 0 0 0 .633-.217c.266-.216 6.516-5.303 8.636-7.658 2.323-2.583 2.308-6.297-.035-8.64Zm-1.41 7.341C17.173 10.971 12.63 14.757 11 16.1c-1.63-1.343-6.17-5.128-7.823-6.964-1.62-1.802-1.636-4.367-.035-5.968a4.181 4.181 0 0 1 2.965-1.226 4.18 4.18 0 0 1 2.965 1.226l1.221 1.221a.94.94 0 0 0 .521.263c.312.067.65-.02.894-.262l1.221-1.222a4.198 4.198 0 0 1 5.93 0c1.601 1.6 1.586 4.166-.034 5.967Z"
                                                            />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="prod-card__slider swiper-slide"
                                                 data-card-slider="data-card-slider">
                                                <div class="prod-card__wrapper swiper-wrapper">
                                                    <div class="prod-card__slide swiper-slide">
                                                        <picture>
                                                            <source srcset="/static/images/common/prod-3.webp"
                                                                    type="image/webp"/>
                                                            <img class="prod-card__img"
                                                                 src="/static/images/common/prod-3.png"
                                                                 width="277" height="181"
                                                                 alt="Фланцевый REON RSV16 вентиль запорный"
                                                                 loading="lazy"/>
                                                        </picture>
                                                    </div>
                                                    <div class="prod-card__slide swiper-slide">
                                                        <picture>
                                                            <source srcset="/static/images/common/prod-3.webp"
                                                                    type="image/webp"/>
                                                            <img class="prod-card__img"
                                                                 src="/static/images/common/prod-3.png"
                                                                 width="277" height="181"
                                                                 alt="Фланцевый REON RSV16 вентиль запорный"
                                                                 loading="lazy"/>
                                                        </picture>
                                                    </div>
                                                    <div class="prod-card__slide swiper-slide">
                                                        <picture>
                                                            <source srcset="/static/images/common/prod-3.webp"
                                                                    type="image/webp"/>
                                                            <img class="prod-card__img"
                                                                 src="/static/images/common/prod-3.png"
                                                                 width="277" height="181"
                                                                 alt="Фланцевый REON RSV16 вентиль запорный"
                                                                 loading="lazy"/>
                                                        </picture>
                                                    </div>
                                                    <div class="prod-card__slide swiper-slide">
                                                        <picture>
                                                            <source srcset="/static/images/common/prod-3.webp"
                                                                    type="image/webp"/>
                                                            <img class="prod-card__img"
                                                                 src="/static/images/common/prod-3.png"
                                                                 width="277" height="181"
                                                                 alt="Фланцевый REON RSV16 вентиль запорный"
                                                                 loading="lazy"/>
                                                        </picture>
                                                    </div>
                                                </div>
                                                <div class="prod-card__slider-pagination swiper-pagination"
                                                     data-card-pagination="data-card-pagination"></div>
                                            </div>
                                        </div>
                                        <div class="prod-card__body">
                                            <div class="prod-card__data">
                                                <div class="prod-card__meta">
                                                    <div class="prod-card__badge">
                                                        <!-- modificators: --accent --accent-alt --alt --grey-alt -->
                                                        <div class="badge">Хит</div>
                                                    </div>
                                                    <div class="prod-card__availability">
                                                        <!-- .unactive - цвет нет в наличии-->
                                                        <div class="availability">В наличии — 14 шт</div>
                                                    </div>
                                                </div>
                                                <a class="prod-card__title" href="javascript:void(0)">Фланцевый
                                                    REON RSV16 вентиль запорный</a>
                                                <div class="prod-card__code">Артикул 0265089</div>
                                            </div>
                                            <div class="prod-card__data prod-card__data--order">
                                                <div class="prod-card__pricing">
                                                    <div class="prod-card__price">880 ₽</div>
                                                    <div class="prod-card__price prod-card__price--old"></div>
                                                </div>
                                                <div class="prod-card__actions">
                                                    <div class="prod-card__counter">
                                                        <div class="b-counter" data-counter="data-counter">
                                                            <button class="b-counter__btn b-counter__btn--prev btn-reset"
                                                                    type="button" aria-label="Меньше">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                     width="12" height="2" fill="none">
                                                                    <path stroke="currentColor"
                                                                          stroke-width="1.5" d="M.75 1h10.5"/>
                                                                </svg>
                                                            </button>
                                                            <input class="b-counter__input" type="number"
                                                                   name="count" value="1"
                                                                   data-count="data-count"/>
                                                            <button class="b-counter__btn b-counter__btn--next btn-reset"
                                                                    type="button" aria-label="Больше">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                     width="12" height="12" fill="none">
                                                                    <path stroke="currentColor"
                                                                          stroke-width="1.5"
                                                                          d="M.75 6h10.5M6 .75v10.5"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="prod-card__cart">
                                                        <button class="btn-cart btn-reset" type="button"
                                                                aria-label="Добавить в корзину"
                                                                data-product-popup="data-product-popup"
                                                                data-src="#order"
                                                                data-label="Фланцевый REON RSV16 вентиль запорный">
                                                                    <span class="btn-cart__icon iconify"
                                                                          data-icon="mynaui:cart"
                                                                          data-width="20"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                        <div class="b-pagination">
                            <a class="b-pagination__link b-pagination__link--btn is-disabled"
                               href="javascript:void(0)" title="Назад">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none">
                                    <path stroke="currentColor" stroke-linecap="round"
                                          stroke-linejoin="round" stroke-width="1.6"
                                          d="M11.459 6.5H1.542m0 0L6.5 11.458M1.542 6.5 6.5 1.542"/>
                                </svg>
                            </a>
                            <a class="b-pagination__link b-pagination__link--btn" href="javascript:void(0)"
                               title="Дальше">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none">
                                    <path stroke="currentColor" stroke-linecap="round"
                                          stroke-linejoin="round" stroke-width="1.6"
                                          d="M1.542 6.5h9.917m0 0-4.958 4.958M11.459 6.5 6.501 1.542"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
