@extends('template')
@section('content')
    <title>Корзина</title>
    <main>
        <section class="cart">
            <form class="cart__container container" action="{{ route('ajax.order') }}" onsubmit="sendOrder(this, event)">
                <div class="cart__row">
                    <div class="cart__title">Корзина</div>
                    <button class="cart__trash btn-reset" type="button" aria-label="Очистить коризну" onclick="purgeCart()">
                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18.5625 4.8125L3.4375 4.81251" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M8.9375 8.9375V14.4375" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M13.0625 8.9375V14.4375" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M17.1875 4.8125V17.875C17.1875 18.0573 17.1151 18.2322 16.9861 18.3611C16.8572 18.4901 16.6823 18.5625 16.5 18.5625H5.5C5.31766 18.5625 5.1428 18.4901 5.01386 18.3611C4.88493 18.2322 4.8125 18.0573 4.8125 17.875V4.8125" stroke="currentColor"
                                  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M14.4375 4.8125V3.4375C14.4375 3.07283 14.2926 2.72309 14.0348 2.46523C13.7769 2.20737 13.4272 2.0625 13.0625 2.0625H8.9375C8.57283 2.0625 8.22309 2.20737 7.96523 2.46523C7.70737 2.72309 7.5625 3.07283 7.5625 3.4375V4.8125" stroke="currentColor"
                                  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span>Очистить корзину</span>
                    </button>
                </div>
                <div class="c-order">
                    <!-- row row--head-->
                    <div class="c-order__row c-order__row--head">
                        <div class="c-order__col c-order__col--col-1">
                            <div class="c-order__label">Фото</div>
                        </div>
                        <div class="c-order__col">
                            <div class="c-order__label">Наименование</div>
                        </div>
                        <div class="c-order__col c-order__col--col-2">
                            <div class="c-order__label">Цена</div>
                        </div>
                        <div class="c-order__col c-order__col--col-2">
                            <div class="c-order__label">Количество</div>
                        </div>
                        <div class="c-order__col c-order__col--col-2">
                            <div class="c-order__label">Сумма</div>
                        </div>
                    </div>
                    @foreach($items as $item)
                        @include('cart.table_row')
                    @endforeach
                    <!-- footer-->
                    <div class="c-order__footer">
                        <div class="c-order__total">Итого:</div>
                        @include('cart.table_row_total')
                        <div class="c-order__count">/ шт</div>
                    </div>
                </div>
                <div class="cart__infos">
                    <div class="order-block">
                        <div class="order-block__title">1. Контактная информация</div>
                        <div class="order-block__grid">
                            <div class="order-block__col">
                                <div class="order-block__fields">
                                    <label class="label-form">
                                        <span data-required="*">Имя</span>
                                        <input class="label-form__input" type="text" name="name" placeholder="Введите имя" required utocomplete="off">
                                    </label>
                                    <label class="label-form">
                                        <span data-required="*">Телефон</span>
                                        <input class="label-form__input" type="tel" name="phone" placeholder="+7 (___) ___-__-__" required autocomplete="off">
                                    </label>
                                    <label class="label-form">
                                        <span data-required="*">Email</span>
                                        <input class="label-form__input" type="text" name="email" placeholder="Введите email" autocomplete="off">
                                    </label>
                                    <label class="label-form">
                                        <span data-required="*">Наименование организации</span>
                                        <input class="label-form__input" type="tel" name="company" placeholder="Введите наименование организации" required autocomplete="off">
                                    </label>
                                </div>
                            </div>
                            <div class="order-block__col">
                                <div class="order-block__text">
                                    <p>Проверьте, пожалуйста, еще раз комплектацию вашего заказа.При необходимости измените заказ.</p>
                                    <p>Если у вас есть дополнительные вопросы или пожелания связанные с организацией доставки в вашу сторону, пожалуйста, воспользуйтесь полем дополнительные комментарии. Вслучае необходимости мы свяжемся с вами.</p>
                                    <p>
                                        <strong>Обращаем Ваше внимание! Работаем только с юридическими лицами.</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="order-block" x-data="{ delivery: true}">
                        <div class="order-block__title">2. Доставка</div>
                        <div class="order-block__view">
                            <div class="order-block__grid">
                                <div class="order-block__col-4" x-show="delivery" x-transition.duration.500ms>
                                    <label class="label-form">
                                        <span data-required="*">Город</span>
                                        <input class="label-form__input" type="text" name="city" placeholder="Введите название города" :required="delivery" autocomplete="off">
                                    </label>
                                </div>
                                <div class="order-block__col-8" x-show="delivery" x-transition.duration.500ms=""></div>
                                <div class="order-block__col-4" x-show="delivery" x-transition.duration.500ms>
                                    <label class="label-form">
                                        <span data-required="*">Улица</span>
                                        <input class="label-form__input" type="text" name="street" placeholder="Введите название улицы" :required="delivery" autocomplete="off">
                                    </label>
                                </div>
                                <div class="order-block__col-4" x-show="delivery" x-transition.duration.500ms>
                                    <label class="label-form">
                                        <span data-required="*">Дом</span>
                                        <input class="label-form__input" type="text" name="home_number" placeholder="Введите номер дома" :required="delivery" autocomplete="off">
                                    </label>
                                </div>
                                <div class="order-block__col-4" x-show="delivery" x-transition.duration.500ms>
                                    <label class="label-form">
                                        <span>Квартира/офис</span>
                                        <input class="label-form__input" type="text" name="apartment_number" placeholder="Введите номер" :required="delivery" autocomplete="off">
                                    </label>
                                </div>
                                <div class="order-block__col-12">
                                    <div class="label-form">
                                        <label class="label-form">
                                            <span>Комментарий</span>
                                            <textarea class="label-form__input" rows="6" name="comment" placeholder="Вы можете оставить комментарий к заказу"></textarea>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cart__bottom">
                        <button class="cart__submit btn-reset" name="submit" aria-label="Оформить">
                            <span>Оформить</span>
                        </button>
                        <div class="cart__policy">Нажимая кнопку «Отправить», вы подтверждаете свое согласие на обработку
                            <a href="{{ route('policy') }}" target="_blank">персональных данных</a>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </main>
@endsection
