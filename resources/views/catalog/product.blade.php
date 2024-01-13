@extends('template')
@section('content')
    @include('blocks.bread')
    <main>
        <section class="page container">
            <div class="page__title">{{ $h1 }}</div>
            <div class="prod">
                <!--._grid-->
                <div class="prod__grid">
                    <div class="prod__view">
                        <div class="prod-view">
                            <div class="prod-view__badge">
                                <!-- modificators: --accent --accent-alt --alt --grey-alt -->
                                @if($product->is_hit)
                                    <div class="badge">Хит</div>
                                @endif
                                @if($product->is_new)
                                    <div class="badge badge--accent">Новинка</div>
                                @endif
                                @if($product->is_discount)
                                    <div class="badge badge--accent-alt">Скидка</div>
                                @endif
                            </div>
                            <div class="prod-view__utils">
                                <div class="prod-view__utils-item">
                                    <!-- .is-active — active color state-->
                                    <button class="utils-btn btn-reset" type="button" aria-label="Добавить к сравнению">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="18" fill="none">
                                            <path fill="currentColor"
                                                  d="M0 2.436c0-.587.544-1.063 1.214-1.063.67 0 1.215.476 1.215 1.063v13.812c0 .587-.544 1.063-1.215 1.063-.67 0-1.214-.476-1.214-1.063V2.436ZM7.286 5.623c0-.587.543-1.062 1.214-1.062.67 0 1.214.475 1.214 1.062v10.625c0 .587-.543 1.063-1.214 1.063-.67 0-1.214-.476-1.214-1.063V5.623ZM14.571 1.373c0-.587.544-1.062 1.215-1.062.67 0 1.214.475 1.214 1.062v14.875c0 .587-.544 1.063-1.214 1.063-.67 0-1.215-.476-1.215-1.063V1.373Z"
                                            />
                                        </svg>
                                    </button>
                                </div>
                                <div class="prod-view__utils-item">
                                    <!-- .is-active — active color state-->
                                    <button class="utils-btn btn-reset" type="button" aria-label="Добавить в избранное">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="19" fill="none">
                                            <path fill="currentColor"
                                                  d="M20.233 1.794a6.143 6.143 0 0 0-8.678 0L11 2.35l-.556-.556a6.142 6.142 0 0 0-8.676 0c-2.344 2.344-2.359 6.058-.036 8.641 2.12 2.355 8.37 7.442 8.634 7.658.18.146.397.217.612.217H11a.937.937 0 0 0 .633-.217c.266-.216 6.516-5.303 8.636-7.658 2.323-2.583 2.308-6.297-.035-8.64Zm-1.41 7.341C17.173 10.971 12.63 14.757 11 16.1c-1.63-1.343-6.17-5.128-7.823-6.964-1.62-1.802-1.636-4.367-.035-5.968a4.181 4.181 0 0 1 2.965-1.226 4.18 4.18 0 0 1 2.965 1.226l1.221 1.221a.94.94 0 0 0 .521.263c.312.067.65-.02.894-.262l1.221-1.222a4.198 4.198 0 0 1 5.93 0c1.601 1.6 1.586 4.166-.034 5.967Z"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @if(count($product->images))
                                <div class="prod-view__slider swiper" data-prod-slider="data-prod-slider">
                                    <div class="prod-view__wrapper swiper-wrapper">
                                        @foreach($product->images as $image)
                                            <a class="prod-view__slide swiper-slide"
                                               href="{{ $image->imageSrc($product->catalog->alias) }}"
                                               data-fancybox="prod-gallery" data-caption="{{ $product->name }}"
                                               title="{{ $product->name }}">
                                                <img class="prod-view__pic no-select"
                                                     src="{{ $image->thumb(3, $product->catalog->alias) }}"
                                                     width="306" height="306" alt="{{ $product->name }}"
                                                     loading="lazy"/>
                                            </a>
                                        @endforeach
                                    </div>
                                    <div class="prod-view__pagination swiper-pagination"
                                         data-prod-pagination="data-prod-pagination"></div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="prod__data" data-id="{{ $product->id }}">
                        <div class="prod__meta">
                            <div class="prod__id">Артикул
                                <span>{{ $product->article }}</span>
                            </div>
                            <div class="prod__availability">
                                <!-- .unactive - цвет нет в наличии-->
                                @if($product->in_stock)
                                    <div class="availability">В наличии</div>
                                @else
                                    <div class="unactive">Нет в наличии</div>
                                @endif
                            </div>
                        </div>
                        <div class="prod__feat">
                            <div class="prod__feat-item">
                                <div class="prod-feat">
                                    <span class="prod-feat__icon iconify" data-icon="bx:shield" data-width="20"></span>
                                    <span class="prod-feat__label">Гарантия 12 месяцев</span>
                                </div>
                            </div>
                            <div class="prod__feat-item">
                                <div class="prod-feat">
                                    <span class="prod-feat__icon iconify" data-icon="icon-park-outline:time"
                                          data-width="20"></span>
                                    <span class="prod-feat__label">Отсрочка до 30 дней</span>
                                </div>
                            </div>
                        </div>
                        <div class="prod__pricing">
                            <div class="prod__price">{{ $product->getFormatPrice() }}&nbsp;₽</div>
                            <div class="prod__price prod__price--old">3&nbsp;502&nbsp;₽</div>
                        </div>
                        <!--._actions(x-data="{ setOrder: false }")-->
                        <div class="prod__actions" x-data="{ orderDialog: false }">
                            <div class="prod__row">
                                <div class="prod__counter">
                                    <div class="b-counter b-counter--wide" data-counter="data-counter">
                                        <button class="b-counter__btn b-counter__btn--prev btn-reset" type="button"
                                                aria-label="Меньше">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="2" fill="none">
                                                <path stroke="currentColor" stroke-width="1.5" d="M.75 1h10.5"/>
                                            </svg>
                                        </button>
                                        <input class="b-counter__input" type="number" name="count" value="1"
                                               data-count="data-count"/>
                                        <button class="b-counter__btn b-counter__btn--next btn-reset" type="button"
                                                aria-label="Больше">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none">
                                                <path stroke="currentColor" stroke-width="1.5"
                                                      d="M.75 6h10.5M6 .75v10.5"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="prod__cart">
                                    <button class="btn-cart btn-cart--wide btn-reset" type="button"
                                            @click="orderDialog = true" :disabled="orderDialog"
                                            aria-label="Добавить в корзину">
                                        <span class="btn-cart__icon iconify" data-icon="mynaui:cart"
                                              data-width="20"></span>
                                        <span class="btn-cart__label">В корзину</span>
                                    </button>
                                </div>
                                <div class="prod__order" :class="orderDialog &amp;&amp; 'is-active'">
                                    <!--.b-order-->
                                    <div class="b-order">
                                        <div class="b-order__top">
                                            <div class="b-order__title">Товар добавлен в корзину</div>
                                            <button class="b-order__close btn-reset" type="button"
                                                    @click="orderDialog = false" aria-label="Закрыть">
                                                <span class="iconify" data-icon="material-symbols:close"
                                                      data-width="15"></span>
                                            </button>
                                        </div>
                                        <div class="b-order__body">
                                            <div class="b-order__row">
                                                <div class="b-order__view">
                                                    <img class="b-order__pic lazy"
                                                         src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                                         data-src="/static/images/common/order.png" width="70"
                                                         height="70" alt=""/>
                                                </div>
                                                <div class="b-order__data">
                                                    <div class="b-order__top">
                                                        <div class="b-order__id">Артикул {{ $product->article }}</div>
                                                        <!-- тут, по всей видимости, удаление заказа-->
                                                        <button class="b-order__close b-order__close--alt btn-reset"
                                                                type="button" @click="orderDialog = false"
                                                                aria-label="Удалить из заказа">
                                                            <span class="iconify" data-icon="material-symbols:close"
                                                                  data-width="15"></span>
                                                        </button>
                                                    </div>
                                                    <div class="b-order__model">{{ $product->name }}
                                                    </div>
                                                    <div class="b-order__controls">
                                                        <div class="b-order__pricing">
                                                            <div class="b-order__price b-order__price--old">3&nbsp;102&nbsp;₽</div>
                                                            <div class="b-order__price">{{ $product->getFormatPrice() }}&nbsp;₽</div>
                                                        </div>
                                                        <div class="b-order__counter">
                                                            <div class="b-counter" data-counter="data-counter">
                                                                <button class="b-counter__btn b-counter__btn--prev btn-reset"
                                                                        type="button" aria-label="Меньше">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12"
                                                                         height="2" fill="none">
                                                                        <path stroke="currentColor" stroke-width="1.5"
                                                                              d="M.75 1h10.5"/>
                                                                    </svg>
                                                                </button>
                                                                <input class="b-counter__input" type="number"
                                                                       name="count" value="1" data-count="data-count"/>
                                                                <button class="b-counter__btn b-counter__btn--next btn-reset"
                                                                        type="button" aria-label="Больше">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12"
                                                                         height="12" fill="none">
                                                                        <path stroke="currentColor" stroke-width="1.5"
                                                                              d="M.75 6h10.5M6 .75v10.5"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="b-order__actions">
                                            <a class="btn-cart btn-cart--wide" href="{{ route('cart') }}"
                                               title="Оформить заказ">
                                                <span class="btn-cart__label">Оформить заказ</span>
                                            </a>
                                            <button class="req-btn btn-reset" type="button" @click="orderDialog = false"
                                                    aria-label="Продолжить покупки">
                                                <span>Продолжить покупки</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="prod__req">
                                <button class="req-btn btn-reset" type="button" data-popup="data-popup"
                                        data-src="#request" aria-label="Отправить заявку">
                                    <span>Отправить заявку</span>
                                </button>
                            </div>
                        </div>
                        @if(count($product->chars))
                            <div class="prod__info">
                                <div class="data-list no-select">
                                    @foreach($product->chars as $char)
                                        @if($loop->iteration <= 5)
                                            <dl class="data-list__list">
                                                <dt class="data-list__term">{{ $char->name }}</dt>
                                                <dd class="data-list__desc">{{ $char->value }}</dd>
                                            </dl>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <!--._tabs-->
                @include('catalog.product_tabs')
            </div>
        </section>
    </main>
@endsection
