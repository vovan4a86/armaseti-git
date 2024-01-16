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
                        @if($banner_before['visibility'] == '1')
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
                                        @if($banner_before['img_text'])
                                            <div class="b-focus__info">
                                            <span class="b-focus__icon iconify" data-icon="icon-park-solid:check-one"
                                                  data-width="18"></span>
                                                <span class="b-focus__label">{{ $banner_before['img_text'] }}</span>
                                            </div>
                                        @endif
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
                                    <a class="link" href="{{ route('new-products') }}" title="Смотреть все новинки">
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
                                                        @foreach($products as $product)
                                                            <div class="b-tabs__item">
                                                                @include('catalog.product_item')
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
                                                <div class="s-feat__icon lazy"
                                                     data-bg="{{ Settings::fileSrc($item['img']) }}"></div>
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
