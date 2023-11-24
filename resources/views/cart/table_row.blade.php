<div class="c-order__row c-order__row--body">
    <div class="c-order__col c-order__col--col-1">
        <a href="{{ array_get($item, 'url', '') }}">
            <img class="c-order__pic lazy" src="/"
                 data-src="{{ array_get($item, 'image', '') }}" width="94" height="74"
                 alt="{{ array_get($item, 'name', '') }}">
        </a>
    </div>
    <div class="c-order__col">
        <div class="c-order__product">{{ array_get($item, 'name', '') }}</div>
    </div>
    <div class="c-order__col c-order__col--col-2">
        @if(array_get($item, 'price'))
            <div class="c-order__price">{{ array_get($item, 'price') }} ₽
                <span>/ шт</span>
            </div>
        @else
            <div class="c-order__out">По запросу</div>
        @endif
    </div>
    <div class="c-order__col c-order__col--col-2">
        <div class="counter" data-counter="data-counter">
            <button class="counter__btn counter__btn--prev btn-reset" type="button" aria-label="Меньше"
                    onclick="cartItemCountUp(this, {{ array_get($item, 'id') }})">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_657_3899)">
                        <path d="M13.0454 5.73724H0.954545C0.427635 5.73724 0 6.16488 0 6.69179V7.32811C0 7.85503 0.427635 8.28266 0.954545 8.28266H13.0454C13.5723 8.28266 14 7.85503 14 7.32811V6.69179C14 6.16488 13.5723 5.73724 13.0454 5.73724Z"
                              fill="#BDBDBD"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_657_3899">
                            <rect width="14" height="14" rx="4" fill="white"/>
                        </clipPath>
                    </defs>
                </svg>
            </button>
            <input class="counter__input" type="number" name="count{{ array_get($item, 'id') }}" value="{{ $item['count'] }}"
                   data-count="data-count"/>
            <button class="counter__btn counter__btn--next btn-reset" type="button" aria-label="Больше"
                    onclick="cartItemCountUp(this, {{ array_get($item, 'id') }})">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_657_3897)">
                        <path d="M12.75 5.75H8.5C8.36194 5.75 8.25 5.63806 8.25 5.5V1.25C8.25 0.559692 7.69031 0 7 0C6.30969 0 5.75 0.559692 5.75 1.25V5.5C5.75 5.63806 5.63806 5.75 5.5 5.75H1.25C0.559692 5.75 0 6.30969 0 7C0 7.69031 0.559692 8.25 1.25 8.25H5.5C5.63806 8.25 5.75 8.36194 5.75 8.5V12.75C5.75 13.4403 6.30969 14 7 14C7.69031 14 8.25 13.4403 8.25 12.75V8.5C8.25 8.36194 8.36194 8.25 8.5 8.25H12.75C13.4403 8.25 14 7.69031 14 7C14 6.30969 13.4403 5.75 12.75 5.75Z"
                              fill="#BDBDBD"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_657_3897">
                            <rect width="14" height="14" rx="4" fill="white"/>
                        </clipPath>
                    </defs>
                </svg>
            </button>
        </div>
    </div>
    <div class="c-order__col c-order__col--col-2">
        @include('cart.table_row_summ')
        <button class="c-order__delete btn-reset" type="button" aria-label="Удалить"
                onclick="deleteCartItem(this, {{ array_get($item, 'id') }})">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.1875 4.8125L4.8125 17.1875" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"/>
                <path d="M17.1875 17.1875L4.8125 4.8125" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round"/>
            </svg>
        </button>
    </div>
</div>
