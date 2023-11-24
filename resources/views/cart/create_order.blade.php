@extends('template')
@section('content')
        <!-- headerIsBlack && 'breadcrumbs--black'-->
        <nav class="breadcrumbs {{ isset($headerIsBlack) ? 'breadcrumbs--black' : null }}">
            <div class="breadcrumbs__container container">
                <ul class="breadcrumbs__list list-reset" itemscope itemtype="https://schema.org/BreadcrumbList">
                    <li class="breadcrumbs__item" itemprop="itemListElement" itemscope
                        itemtype="https://schema.org/ListItem">
                        <a class="breadcrumbs__link" href="{{ route('main') }}" itemprop="item">
                            <span itemprop="name">Главная</span>
                            <meta itemprop="position" content="1">
                        </a>
                    </li>
                    <li class="breadcrumbs__item" itemprop="itemListElement" itemscope
                        itemtype="https://schema.org/ListItem">
                        <a class="breadcrumbs__link breadcrumbs__link"
                           href="#" itemprop="item">
                            <span itemprop="name">Оформление заказа</span>
                            <meta itemprop="position" content="{{ 2 }}">
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    <main>
        <form class="cart" action="{{ route('ajax.order') }}" onsubmit="sendOrder(this, event)">
            <div class="cart__container container">
                <div class="cart__layout">
                    <div class="cart__head">
                        <div class="cart__title">Оформление заказа</div>
                    </div>
                </div>
                <div class="cart__layout">
                    <div class="cart__body">
                        <div class="cart__rows">
                            <div class="cart-block">
                                <div class="cart-block__title">1. Контактные данные</div>
                                <div class="cart-block__data" x-data="{ company: true }">
                                    <div class="cart-block__type">
                                        <div class="cart-block__label">Выберите тип плательщика</div>
                                        <div class="cart-block__types">
                                            <div class="radios-type">
                                                <label class="radios-type__label">
                                                    <input class="radios-type__input" type="radio"
                                                           name="payer_type" value="1"
                                                           @click="company = false">
                                                    <span class="radios-type__text">Физическое лицо</span>
                                                </label>
                                                <label class="radios-type__label">
                                                    <input class="radios-type__input" type="radio"
                                                           name="payer_type" value="2" checked
                                                           @click="company = true">
                                                    <span class="radios-type__text">Юридическое лицо</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="cart-block__userdata">
                                        <div class="userdata">
                                            <div class="userdata__grid">
                                                <label class="userdata__label">
                                                    <span class="userdata__text" data-end="*">Имя</span>
                                                    <input class="userdata__input" type="text" name="name"
                                                           placeholder="Ваше имя" required>
                                                </label>
                                                <label class="userdata__label">
                                                    <span class="userdata__text">Email</span>
                                                    <input class="userdata__input" type="text" name="email"
                                                           placeholder="Введите Email">
                                                </label>
                                                <label class="userdata__label">
                                                    <span class="userdata__text" data-end="*">Телефон</span>
                                                    <input class="userdata__input" type="tel" name="phone"
                                                           placeholder="+7 (___) ___-__-__" required>
                                                </label>
                                                <label class="userdata__label" x-show="company" x-transition>
                                                    <span class="userdata__text"
                                                          data-end="*">Название организации</span>
                                                    <input class="userdata__input" type="text" name="company"
                                                           placeholder="Введите название" :required="company">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="cart-block">
                                <div class="cart-block__title">2. Доставка</div>
                                <div class="cart-data">
                                    <div class="cart-data__title">Выберите способ доставки</div>
                                    <div class="cart-data__grid">
                                        @foreach($delivery as $item)
                                            <div class="cart-data__col">
                                                <div class="cart-radio">
                                                    <label class="cart-radio__label">
                                                        <input class="cart-radio__input" type="radio" name="delivery_item_id"
                                                               value="{{ $item->id }}" {{ $loop->first ? 'checked' : null }}>
                                                        <span class="cart-radio__body">
                                                            <span class="cart-radio__title">{{ $item->name }}</span>
                                                            @if($item->description)
                                                                <span class="cart-radio__row">
                                                                    <span class="cart-radio__data">{{ $item->description }}</span>
                                                                </span>
                                                            @endif
                                                            @if($item->text)
                                                                <span class="cart-radio__row">
                                                                    <span class="cart-radio__data">{{ $item->text }}</span>
                                                                </span>
                                                            @endif
                                                            @if($item->price)
                                                                <span class="cart-radio__row">
                                                                    <span class="cart-radio__data">{{ $item->price }}</span>
                                                                </span>
                                                            @endif
                                                            @if($item->free)
                                                                <span class="cart-radio__row">
                                                                    <span class="cart-radio__data">{{ $item->free }}</span>
                                                                </span>
                                                            @endif
                                                    </span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="cart-block">
                                <div class="cart-block__title">3. Оплата</div>
                                <div class="cart-data">
                                    <div class="cart-data__title">Выберите способ оплаты</div>
                                    <div class="cart-data__grid">
                                        <div class="cart-data__col">
                                            <div class="cart-radio">
                                                <label class="cart-radio__label">
                                                    <input class="cart-radio__input" type="radio" name="payment"
                                                           value="1">
                                                    <span class="cart-radio__body">
															<span class="cart-radio__title">Наличный расчет</span>
															<span class="cart-radio__row">
																<span class="cart-radio__data">На месте при получении, либо по предоплате.</span>
															</span>
														</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="cart-data__col">
                                            <div class="cart-radio">
                                                <label class="cart-radio__label">
                                                    <input class="cart-radio__input" type="radio" name="payment"
                                                           value="2" checked>
                                                    <span class="cart-radio__body">
															<span class="cart-radio__title">Безналичный расчет</span>
															<span class="cart-radio__row">
																<span class="cart-radio__data">Оплата производятся на основании выставленного счета.</span>
															</span>
														</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cart__aside">
                        <div class="cart-view">
                            <div class="cart-view__head">Ваш заказ</div>
                            <div class="cart-view__body">
                                @foreach($items as $item)
                                    <div class="cart-view__row">
                                        <dl class="dl-reset">
                                            <dt>{{ $item['name'] }}</dt>
                                            @if($item['weight'] > 0)
                                                <dd>{{ floor($item['current_price'] * $item['weight']) }} руб.</dd>
                                            @else
                                                <dd>{{ floor($item['current_price'] * $item['count']) }} руб.</dd>
                                            @endif
                                        </dl>
                                    </div>
                                @endforeach
                                <div class="cart-view__row">
                                    <dl class="dl-reset">
                                        <dt>Общий вес товаров</dt>
                                        <dd>{{ \Fanky\Admin\Cart::total_weight() }} кг</dd>
                                    </dl>
                                </div>
                                <div class="cart-view__total">
                                    <dl class="dl-reset">
                                        <dt>Итого</dt>
                                        <dd>{{ \Fanky\Admin\Cart::sum() }} руб.</dd>
                                    </dl>
                                </div>
                                <div class="cart-view__callback">
                                    <label class="checkbox-callback">
                                        <input class="checkbox-callback__input" type="checkbox" name="callback" checked>
                                        <span class="checkbox-callback__body">
												<span class="checkbox-callback__label">Нужен звонок оператора для подтверждения заказа</span>
												<span class="checkbox-callback__text">В связи со сложившейся ситуацией на рынке и увеличенным спросом наличие товаров не гарантируется и цена по не оплаченным заказам может измениться. С Вами обязательно свяжется продавец-консультант, для подтверждения количества и цены товаров!</span>
											</span>
                                    </label>
                                </div>
                                <div class="cart-view__submit">
                                    <button class="button button--primary button--small" type="submit" name="submit"
                                            aria-label="Подтвердить заказ">
                                        <span>Подтвердить заказ</span>
                                    </button>
                                </div>
                                <div class="cart-view__policy">Завершая оформление Заказа, я подтверждаю свою
                                    дееспособность, даю согласие на обработку моих персональных данных и подтверждаю
                                    ознакомление с
                                    <a href="{{ route('policy') }}" target="_blank">«Соглашением с покупателем»</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>
@endsection
