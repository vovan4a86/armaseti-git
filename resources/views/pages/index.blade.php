@extends('template')
@section('content')
    <!--.layout-->
    <div class="layout">
        <div class="layout__container container">
            @include('blocks.aside')
            <!--main._main-->
            <main class="layout__main">
                <div class="page">
                    @if($banner_before = Settings::get('banner_before'))
                        @if($banner_before['disable'] == '')
                            <div class="page__item">
                                <!--.b-focus-->
                                <div class="b-focus">
                                    <div class="b-focus__body">
                                        <a class="b-focus__title"
                                           href="{{ $banner_before['url'] ? url($banner_before['url']) : route('main') }}"
                                           title="{{ $banner_before['title'] }}">{{ $banner_before['title'] }}</a>
                                        <div class="b-focus__out">{{ $banner_before['subtitle'] }}</div>
                                    </div>
                                    <div class="b-focus__view">
                                        @if($img = $banner_before['img'])
                                            <img class="b-focus__img" src="{{ Settings::fileSrc($img) }}"
                                                 width="162" height="125" alt="{{ $banner_before['title'] }}"
                                                 loading="lazy"/>
                                        @endif
                                        <div class="b-focus__info">
                                            <span class="b-focus__icon iconify" data-icon="icon-park-solid:check-one"
                                                  data-width="18"></span>
                                            <span class="b-focus__label">{{ $banner_before['diameter'] }}</span>
                                        </div>
                                    </div>
                                    <span class="b-focus__pseudo iconify" data-icon="maki:arrow" data-width="30"></span>
                                </div>
                            </div>
                        @endif
                    @endif
                    @if(count($catalog_on_main))
                        <div class="page__item">
                            <section class="s-catalog">
                                <div class="s-catalog__grid">
                                    @foreach($catalog_on_main as $category)
                                        <div class="s-catalog__item">
                                            <div class="s-card">
                                                <a class="s-card__title" href="{{ $category->url }}"
                                                   title="{{ $category->name }}">{{ $category->name }}</a>
                                                <div class="s-card__body">
                                                    <div class="s-card__count">{{ $category->getRecurseProductsCountWithEnd() }}</div>
                                                    <div class="s-card__view">
                                                        <picture>
                                                            <source srcset="/static/images/common/cat-10.webp"
                                                                    type="image/webp"/>
                                                            <img class="s-card__pic"
                                                                 src="/static/images/common/cat-10.png"
                                                                 width="130" height="130" alt="{{ $category->name }}"
                                                                 loading="lazy"/>
                                                        </picture>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($loop->iteration == 9 && $banner_request = Settings::get('banner_request'))
                                            <div class="s-catalog__item s-catalog__item--wide">
                                                <div class="b-req lazy" data-bg="/static/images/common/b-req-bg.jpg">
                                                    <div class="b-req__body">
                                                        <div class="b-req__title">{{ $banner_request['title'] }}</div>
                                                        <div class="b-req__text">{{ $banner_request['text'] }}</div>
                                                    </div>
                                                    <div class="b-req__actions">
                                                        <button class="btn btn--accent btn--wide btn-reset"
                                                                type="button" data-popup="data-popup"
                                                                data-src="#request" aria-label="Оставить заявку">
                                                            <span class="btn__label">Оставить заявку</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </section>
                        </div>
                    @endif
                    @if(count($new_products_categories))
                        <div class="page__item page__item--wide">
                            <!--section.s-prods-->
                            <section class="s-prods">
                                <div class="s-prods__head">
                                    <div class="title">Новинки</div>
                                    <a class="link" href="javascript:void(0)" title="Смотреть все новинки">
                                        <span class="link__label">Смотреть все новинки</span>
                                        <span class="link__icon iconify" data-icon="uil:arrow-right"
                                              data-width="16"></span>
                                    </a>
                                </div>
                                <div class="s-prods__tabs">
                                    <!--.b-tabs.tabs(data-tabs)-->
                                    <div class="b-tabs tabs" data-tabs>
                                        <div class="b-tabs__nav tabs__nav">
                                            @foreach($new_products_categories as $category => $products)
                                                <div class="b-tabs__link tabs__link {{ $loop->first ? 'is-active' : null }}"
                                                     data-open="{{ $category }}">{{ $category }}</div>
                                            @endforeach
                                        </div>
                                        <div class="b-tabs__views tabs__views">
                                            @foreach($new_products_categories as $category => $products)
                                                <div class="b-tabs__view tabs__view {{ $loop->first ? 'is-active' : null }}"
                                                     data-view="{{ $category }}">
                                                    <div class="b-tabs__grid">
                                                        @foreach($products as $item)
                                                            <div class="b-tabs__item">
                                                                <!--form.prod-card(action="#")-->
                                                                <form class="prod-card" action="#">
                                                                    <input type="hidden" name="id"
                                                                           value="{{ $item->id }}"/>
                                                                    <input type="hidden" name="product"
                                                                           value="{{ $item->name }}"/>
                                                                    <input type="hidden" name="count" value="1"/>
                                                                    <div class="prod-card__utils">
                                                                        <div class="prod-card__utils-item">
                                                                            <!-- .is-active — active color state-->
                                                                            <button class="utils-btn btn-reset"
                                                                                    type="button"
                                                                                    aria-label="Добавить к сравнению">
                                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                                     width="17" height="18" fill="none">
                                                                                    <path fill="currentColor"
                                                                                          d="M0 2.436c0-.587.544-1.063 1.214-1.063.67 0 1.215.476 1.215 1.063v13.812c0 .587-.544 1.063-1.215 1.063-.67 0-1.214-.476-1.214-1.063V2.436ZM7.286 5.623c0-.587.543-1.062 1.214-1.062.67 0 1.214.475 1.214 1.062v10.625c0 .587-.543 1.063-1.214 1.063-.67 0-1.214-.476-1.214-1.063V5.623ZM14.571 1.373c0-.587.544-1.062 1.215-1.062.67 0 1.214.475 1.214 1.062v14.875c0 .587-.544 1.063-1.214 1.063-.67 0-1.215-.476-1.215-1.063V1.373Z"/>
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                        <div class="prod-card__utils-item">
                                                                            <!-- .is-active — active color state-->
                                                                            <button class="utils-btn btn-reset"
                                                                                    type="button"
                                                                                    aria-label="Добавить в избранное">
                                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                                     width="22" height="19" fill="none">
                                                                                    <path fill="currentColor"
                                                                                          d="M20.233 1.794a6.143 6.143 0 0 0-8.678 0L11 2.35l-.556-.556a6.142 6.142 0 0 0-8.676 0c-2.344 2.344-2.359 6.058-.036 8.641 2.12 2.355 8.37 7.442 8.634 7.658.18.146.397.217.612.217H11a.937.937 0 0 0 .633-.217c.266-.216 6.516-5.303 8.636-7.658 2.323-2.583 2.308-6.297-.035-8.64Zm-1.41 7.341C17.173 10.971 12.63 14.757 11 16.1c-1.63-1.343-6.17-5.128-7.823-6.964-1.62-1.802-1.636-4.367-.035-5.968a4.181 4.181 0 0 1 2.965-1.226 4.18 4.18 0 0 1 2.965 1.226l1.221 1.221a.94.94 0 0 0 .521.263c.312.067.65-.02.894-.262l1.221-1.222a4.198 4.198 0 0 1 5.93 0c1.601 1.6 1.586 4.166-.034 5.967Z"/>
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    @if(count($item->images))
                                                                        <div class="prod-card__slider swiper-slide"
                                                                             data-card-slider="data-card-slider">
                                                                            <div class="prod-card__wrapper swiper-wrapper">
                                                                                @foreach($item->images as $image)
                                                                                    <div class="prod-card__slide swiper-slide">
                                                                                        <picture>
                                                                                            <img class="prod-card__img"
                                                                                                 src="{{ $image->thumb(2, $item->catalog->alias) }}"
                                                                                                 width="277"
                                                                                                 height="181"
                                                                                                 alt="{{ $item->name }}"
                                                                                                 loading="lazy"/>
                                                                                        </picture>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                            <div class="prod-card__slider-pagination swiper-pagination"
                                                                                 data-card-pagination="data-card-pagination"></div>
                                                                        </div>
                                                                    @endif
                                                                    <div class="prod-card__body">
                                                                        <div class="prod-card__availability">
                                                                            <!-- .unactive - цвет нет в наличии-->
                                                                            @if($item->in_stock)
                                                                                <div class="availability">В наличии
                                                                                </div>
                                                                            @else
                                                                                <div class="unactive">Нет в наличии
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                        <div class="prod-card__title">{{ $item->name }}</div>
                                                                        <div class="prod-card__code">
                                                                            Артикул {{ $item->article }}</div>
                                                                        <div class="prod-card__price">{{ number_format($item->price, 0, '.', ' ') }}
                                                                            ₽
                                                                        </div>
                                                                        <div class="prod-card__actions">
                                                                            <div class="prod-card__counter">
                                                                                <div class="b-counter"
                                                                                     data-counter="data-counter">
                                                                                    <button class="b-counter__btn b-counter__btn--prev btn-reset"
                                                                                            type="button"
                                                                                            aria-label="Меньше">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                                             width="12" height="2"
                                                                                             fill="none">
                                                                                            <path stroke="currentColor"
                                                                                                  stroke-width="1.5"
                                                                                                  d="M.75 1h10.5"/>
                                                                                        </svg>
                                                                                    </button>
                                                                                    <input class="b-counter__input"
                                                                                           type="number" name="count"
                                                                                           value="1"
                                                                                           data-count="data-count"/>
                                                                                    <button class="b-counter__btn b-counter__btn--next btn-reset"
                                                                                            type="button"
                                                                                            aria-label="Больше">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                                             width="12" height="12"
                                                                                             fill="none">
                                                                                            <path stroke="currentColor"
                                                                                                  stroke-width="1.5"
                                                                                                  d="M.75 6h10.5M6 .75v10.5"/>
                                                                                        </svg>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                            <div class="prod-card__cart">
                                                                                <button class="btn-cart btn-reset"
                                                                                        type="button"
                                                                                        aria-label="Добавить в корзину">
                                                                                    <span class="icon iconify"
                                                                                          data-icon="mynaui:cart"
                                                                                          data-width="20"></span>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    @endif
                    @if($main_features = Settings::get('main_features'))
                        <div class="page__item page__item--wide">
                        <!--section.s-feat-->
                        <section class="s-feat">
                            <div class="s-feat__head">
                                <div class="title">Почему выгодно покупать у нас?</div>
                            </div>
                            <div class="s-feat__grid">
                                @foreach($main_features as $item)
                                    <div class="s-feat__item">
                                        @if($item['img'])
                                            <div class="s-feat__icon lazy" data-bg="{{ Settings::fileSrc($item['img']) }}"></div>
                                        @endif
                                        <div class="s-feat__title">{{ $item['text'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    </div>
                    @endif
                    @if (count($news))
                        <div class="page__item page__item--wide">
                            <!--section.s-news-->
                            <section class="s-news">
                                <div class="s-news__head">
                                    <div class="title">Новости</div>
                                    <a class="btn btn--accent" href="{{ route('news') }}" title="Все новости">
                                        <span>Все новости</span>
                                    </a>
                                </div>
                                <div class="s-news__grid">
                                    @foreach($news as $item)
                                        <div class="s-news__item">
                                            <div class="news-card">
                                                <div class="news-card__view">
                                                    <img class="news-card__pic"
                                                         src="{{ $item->image ? $item->thumb(2) : \Fanky\Admin\Models\News::NO_IMAGE }}"
                                                         width="430" height="255" alt="{{ $item->name }}"
                                                         loading="lazy"/>
                                                </div>
                                                <div class="news-card__body">
                                                    <div class="news-card__date">{{ $item->dateFormat() }}</div>
                                                    <div class="news-card__title">{{ $item->name }}</div>
                                                    <a class="news-card__link" href="{{ $item->url }}" title="Читать">
                                                        <span class="news-card__link-label">Читать</span>
                                                        <span class="news-card__link-icon iconify"
                                                              data-icon="uil:arrow-right" data-width="16"></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        </div>
                    @endif
                    <div class="page__item page__item--wide">
                        <!--.b-sub-->
                        <div class="b-sub lazy" data-bg="/static/images/common/sub-bg.jpg">
                            <div class="b-sub__body">
                                <div class="b-sub__title">Узнавайте первыми!</div>
                                <div class="b-sub__text">Подпишитесь на акции и обновления</div>
                            </div>
                            <div class="b-sub__action">
                                <button class="btn btn--accent btn--wide btn-reset" type="button"
                                        data-popup="data-popup" data-src="#subscribe" aria-label="Подписаться">
                                    <span class="btn__label">Подписаться</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    @include('blocks.request')
@stop
