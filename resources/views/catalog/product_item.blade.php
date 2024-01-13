<!--._item(:class="listView && 'is-active'")-->
<div class="cat-view__item" :class="listView &amp;&amp; 'is-active'">
    <!--form.prod-card(action="#")-->
    <form class="prod-card" action="#">
        <input type="hidden" name="id" />
        <input type="hidden" name="product" value="{{ $product->name }}" />
        <input type="hidden" name="count" value="1" />
        <div class="prod-card__view">
            <div class="prod-card__utils">
                <div class="prod-card__utils-item">
                    <!-- .is-active — active color state-->
                    <button class="utils-btn btn-reset" type="button" aria-label="Добавить к сравнению">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="18" fill="none">
                            <path fill="currentColor" d="M0 2.436c0-.587.544-1.063 1.214-1.063.67 0 1.215.476 1.215 1.063v13.812c0 .587-.544 1.063-1.215 1.063-.67 0-1.214-.476-1.214-1.063V2.436ZM7.286 5.623c0-.587.543-1.062 1.214-1.062.67 0 1.214.475 1.214 1.062v10.625c0 .587-.543 1.063-1.214 1.063-.67 0-1.214-.476-1.214-1.063V5.623ZM14.571 1.373c0-.587.544-1.062 1.215-1.062.67 0 1.214.475 1.214 1.062v14.875c0 .587-.544 1.063-1.214 1.063-.67 0-1.215-.476-1.215-1.063V1.373Z"
                            />
                        </svg>
                    </button>
                </div>
                <div class="prod-card__utils-item">
                    <!-- .is-active — active color state-->
                    <button class="utils-btn btn-reset" type="button" aria-label="Добавить в избранное">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="19" fill="none">
                            <path fill="currentColor" d="M20.233 1.794a6.143 6.143 0 0 0-8.678 0L11 2.35l-.556-.556a6.142 6.142 0 0 0-8.676 0c-2.344 2.344-2.359 6.058-.036 8.641 2.12 2.355 8.37 7.442 8.634 7.658.18.146.397.217.612.217H11a.937.937 0 0 0 .633-.217c.266-.216 6.516-5.303 8.636-7.658 2.323-2.583 2.308-6.297-.035-8.64Zm-1.41 7.341C17.173 10.971 12.63 14.757 11 16.1c-1.63-1.343-6.17-5.128-7.823-6.964-1.62-1.802-1.636-4.367-.035-5.968a4.181 4.181 0 0 1 2.965-1.226 4.18 4.18 0 0 1 2.965 1.226l1.221 1.221a.94.94 0 0 0 .521.263c.312.067.65-.02.894-.262l1.221-1.222a4.198 4.198 0 0 1 5.93 0c1.601 1.6 1.586 4.166-.034 5.967Z"
                            />
                        </svg>
                    </button>
                </div>
            </div>
            @if(count($product->images))
                <div class="prod-card__slider swiper-slide" data-card-slider="data-card-slider">
                <div class="prod-card__wrapper swiper-wrapper">
                    @foreach($product->images as $image)
                        <div class="prod-card__slide swiper-slide">
                            <picture>
                                <img class="prod-card__img" src="{{ $image->thumb(2, $product->catalog->alias) }}"
                                     width="277" height="181" alt="{{ $product->name }}" loading="lazy" />
                            </picture>
                        </div>
                    @endforeach
                </div>
                <div class="prod-card__slider-pagination swiper-pagination" data-card-pagination="data-card-pagination"></div>
            </div>
            @endif
        </div>
        <div class="prod-card__body">
            <div class="prod-card__data">
                <div class="prod-card__meta">
                    <div class="prod-card__badge">
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
                    <div class="prod-card__availability">
                        <!-- .unactive - цвет нет в наличии-->
                        @if($product->in_stock)
                            <div class="availability">В наличии</div>
                        @else
                            <div class="unactive">Нет в наличии</div>
                        @endif
                    </div>
                </div>
                <a class="prod-card__title" href="{{ $product->url }}">{{ $product->name }}</a>
                <div class="prod-card__code">Артикул {{ $product->article }}</div>
            </div>
            <div class="prod-card__data prod-card__data--order">
                <div class="prod-card__pricing">
                    <div class="prod-card__price">{{ $product->price }} ₽</div>
                    <div class="prod-card__price prod-card__price--old"></div>
                </div>
                <div class="prod-card__actions">
                    <div class="prod-card__counter">
                        <div class="b-counter" data-counter="data-counter">
                            <button class="b-counter__btn b-counter__btn--prev btn-reset" type="button" aria-label="Меньше">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="2" fill="none">
                                    <path stroke="currentColor" stroke-width="1.5" d="M.75 1h10.5" />
                                </svg>
                            </button>
                            <input class="b-counter__input" type="number" name="count" value="1" data-count="data-count" />
                            <button class="b-counter__btn b-counter__btn--next btn-reset" type="button" aria-label="Больше">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none">
                                    <path stroke="currentColor" stroke-width="1.5" d="M.75 6h10.5M6 .75v10.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="prod-card__cart">
                        <button class="btn-cart btn-reset" type="button" aria-label="Добавить в корзину"
                                data-product-popup="data-product-popup" data-src="#order" data-label="{{ $product->name }}">
                            <span class="btn-cart__icon iconify" data-icon="mynaui:cart" data-width="20"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
