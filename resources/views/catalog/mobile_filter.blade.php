<div class="page__filter">
    <div class="filter-view" x-data="{ filterIsOpen: false }">
        <div class="filter-view__action">
            <button class="filter-view__btn btn-reset" type="button" aria-label="Подбор оборудования" @click="filterIsOpen = true">
                <span class="filter-view__btn-label">Подбор оборудования</span>
                <span class="filter-view__btn-icon iconify" data-icon="fluent:filter-32-filled"></span>
            </button>
        </div>
        <div class="filter-view__aside" :class="filterIsOpen &amp;&amp; 'is-active'">
            <!--form.b-filter-->
            <form class="b-filter" action="{{ $category->url }}" data-current-url="{{ URL::full() }}">
                <div class="b-filter__item">
                    <div class="b-filter__title is-active">Цена, ₽</div>
                    <div class="b-filter__body is-active" data-range-slider="data-range-slider">
                        <div class="b-filter__slider range-slider">
                            <input class="b-filter__slider-price js-range-slider" type="text" />
                        </div>
                        <div class="b-filter__slider-controls extra-controls">
                            <label class="b-filter__slider-label" data-caption="Мин">
                                <input class="b-filter__slider-input js-input-from" type="text"
                                       name="price-from" value="0" data-price-from="0" />
                            </label>
                            <label class="b-filter__slider-label" data-caption="Макс">
                                <input class="b-filter__slider-input js-input-to"
                                       type="text" name="price-to" value="{{ $filter_max_price }}"
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
                                <input class="c-radio__input" type="radio" name="availability" value="В наличии" checked="checked" />
                                <span class="c-radio__box"></span>
                                <span class="c-radio__label">В наличии</span>
                            </label>
                            <label class="c-radio">
                                <input class="c-radio__input" type="radio" name="availability" value="Под заказ" />
                                <span class="c-radio__box"></span>
                                <span class="c-radio__label">Под заказ</span>
                            </label>
                        </div>
                    </div>
                </div>

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
                        @endif
                        <div class="b-filter__action">
                            <button class="b-filter__btn btn-reset"
                                    type="button" aria-label="Показать все">Показать все</button>
                        </div>
                    </div>
                </div>
                @endforeach

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
            <button class="filter-view__close btn-reset" type="button"
                    aria-label="Закрыть подбор" @click="filterIsOpen = false">
                <span class="iconify" data-icon="carbon:close-filled" data-width="24"></span>
            </button>
        </div>
        <div class="filter-view__aside filter-view__aside--backdrop"
             :class="filterIsOpen &amp;&amp; 'is-active'" @click="filterIsOpen = false"></div>
    </div>
</div>
