<div class="b-cards__item">
    <div class="p-card">
        @if($item->price && $item->getRecourseDiscountAmount())
            <div class="p-card__badge badge">Лучшая цена</div>
        @endif
        <div class="p-card__preview">
            <a href="{{ $item->url }}"
               title="{{ $item->name }}">
                <img class="p-card__img"
                     src="{{ $item->image()->first() ? $item->image()->first()->image : $item->showAnyImage() }}"
                     data-src="{{ $item->image()->first() ? $item->image()->first()->image : $item->showAnyImage() }}"
                     width="227" height="162" alt="{{ $item->name }}"/>
            </a>
        </div>
        <div class="p-card__code">Код: {{ $item->article }}</div>
        <a class="p-card__body" href="{{ $item->url }}">{{ $item->name }}</a>
        <div class="p-card__bottom">
            <div class="p-card__pricing">
                @if($item->price)
                    @if($item->getRecourseDiscountAmount())
                        <div class="p-card__discounts">
                            <span data-end="₽/{{ $item->getRecourseMeasure() }}">{{ round($item->getPriceWithDiscount()) }}</span>
                            <div class="p-card__value">
                                -{{ $item->getRecourseDiscountAmount() }}%
                            </div>
                        </div>
                    @endif
                    <div class="p-card__current"
                         data-end="/ {{ $item->getRecourseMeasure() }}">{{ $item->price }}
                        ₽
                    </div>
                @endif
            </div>
            <div class="p-card__action">
                <button class="btn btn--primary btn--small btn-reset"
                        data-count="{{ $item->price ? 1 : 0 }}"
                        onclick="addToCart(this, {{ $item->id }}, event)">
                    <span>Заказать</span>
                </button>
            </div>
        </div>
    </div>
</div>
