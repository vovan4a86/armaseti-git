<div class="b-cart__item" data-id="{{ $item['id'] }}">
    <input type="hidden" name="{{ $item['name'] }}" />
    <div class="cart-item">
        <div class="cart-item__view">
            <img class="cart-item__pic no-select" src="{{ $item['image'] }}" width="100" height="100" alt="{{ $item['name'] }}" />
        </div>
        <div class="cart-item__body">
            <div class="cart-item__data">
                <div class="cart-item__availability">
                    <!-- .unactive - цвет нет в наличии-->
                    <div class="availability">В наличии</div>
                </div>
                <a class="cart-item__title" href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                <div class="cart-item__code">Артикул {{ $item['article'] }}</div>
            </div>
            <div class="cart-item__info">
                <div class="cart-item__pricing">
                    <div class="cart-item__price">{{ number_format($item['price'], 0, ',', ' ') }} ₽</div>
                    <div class="cart-item__price cart-item__price--old">13 502 ₽</div>
                </div>
                <div class="cart-item__counter">
                    <div class="b-counter" data-counter="data-counter">
                        <button class="b-counter__btn b-counter__btn--prev btn-reset" type="button" aria-label="Меньше">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="2" fill="none">
                                <path stroke="currentColor" stroke-width="1.5" d="M.75 1h10.5" />
                            </svg>
                        </button>
                        <input class="b-counter__input" type="number" name="count" value="{{ $item['count'] }}" data-count="data-count" />
                        <button class="b-counter__btn b-counter__btn--next btn-reset" type="button" aria-label="Больше">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none">
                                <path stroke="currentColor" stroke-width="1.5" d="M.75 6h10.5M6 .75v10.5" />
                            </svg>
                        </button>
                    </div>
                </div>
                @include('cart.table_row_summary')
            </div>
            <div class="cart-item__delete">
                <button class="cart-item__close btn-reset" type="button" aria-label="Удалить из заказа">
                    <span class="iconify" data-icon="material-symbols:close" data-width="20"></span>
                </button>
            </div>
        </div>
    </div>
</div>
