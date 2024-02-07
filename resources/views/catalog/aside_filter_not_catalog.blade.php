<aside class="layout__aside">
    <div class="layout__item">
        <form class="b-filter b-cat" action="{{ $route }}" data-cat="{{ $cat }}">
            <div class="b-filter__item">
                <div class="b-filter__title is-active">Цена, ₽</div>
                <div class="b-filter__body is-active" data-range-slider="data-range-slider">
                    <div class="b-filter__slider range-slider">
                        <input class="b-filter__slider-price js-range-slider" type="text" />
                    </div>
                    <div class="b-filter__slider-controls extra-controls">
                        <label class="b-filter__slider-label" data-caption="Мин">
                            <input class="b-filter__slider-input js-input-from"
                                   type="text" name="price_from" value="0" data-price-from="0" />
                        </label>
                        <label class="b-filter__slider-label" data-caption="Макс">
                            <input class="b-filter__slider-input js-input-to"
                                   type="text" name="price_to" value="{{ $filter_max_price }}"
                                   data-price-to="{{ $filter_max_price }}" />
                        </label>
                    </div>
                </div>
            </div>
            <div class="b-filter__item">
                <div class="b-filter__title is-active">Наличие</div>
                <div class="b-filter__body is-active">
                    <div class="b-filter__boxes">
                        <label class="c-radio">
                            <input class="c-radio__input" type="radio" name="in_stock" value="1" checked="checked" />
                            <span class="c-radio__box"></span>
                            <span class="c-radio__label">В наличии</span>
                        </label>
                        <label class="c-radio">
                            <input class="c-radio__input" type="radio" name="in_stock" value="0" />
                            <span class="c-radio__box"></span>
                            <span class="c-radio__label">Под заказ</span>
                        </label>
                    </div>
                </div>
            </div>

            @if(isset($filters_list) && count($filters_list))
                @foreach($filters_list as $name => $filter)
                <div class="b-filter__item">
                <div class="b-filter__title">{{ $name }}</div>
                <div class="b-filter__body">
                    <div class="b-filter__boxes">
                        @foreach($filter['values'] as $value)
                            @if($loop->iteration <= 6)
                                <label class="chbx">
                                    <input class="chbx__input" type="checkbox"
                                           name="{{ $filter['translit'] }}[]" value="{{ $value }}" />
                                    <span class="chbx__box"></span>
                                    <span class="chbx__label">{{ $value }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                    @if(count($filter['values']) > 6)
                        <div class="b-filter__hidden">
                            <div class="b-filter__boxes">
                                @foreach($filter['values'] as $value)
                                    @if($loop->iteration > 6)
                                        <label class="chbx">
                                            <input class="chbx__input" type="checkbox"
                                                   name="{{ $filter['translit'] }}[]" value="{{ $value }}" />
                                            <span class="chbx__box"></span>
                                            <span class="chbx__label">{{ $value }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="b-filter__action">
                            <button class="b-filter__btn btn-reset"
                                    type="button" aria-label="Показать все">Показать все</button>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
            @endif

            <div class="b-filter__actions">
                <button class="b-filter__submit btn-reset" aria-label="Применить">
                    <span>Применить</span>
                </button>
                <button class="b-filter__submit b-filter__submit--reset btn-reset"
                        type="reset" aria-label="Сбросить">
                    <span>Сбросить</span>
                </button>
            </div>
        </form>
    </div>
    @include('blocks.aside_banner')
</aside>
