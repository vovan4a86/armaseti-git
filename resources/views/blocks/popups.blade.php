{{--<div class="popup" id="is-done" style="display: none">--}}
{{--    <div class="popup__container">--}}
{{--        <div class="popup__heading">--}}
{{--            <div class="page-title oh">--}}
{{--                <span data-aos="fade-down" data-aos-duration="900" data-custom-title="data-custom-title">Отправлено!</span>--}}
{{--            </div>--}}
{{--            <div class="popup__label" data-custom-label>Отлично, в течении 15 минут, мы с вами свяжемся</div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<div class="popup" id="get-price" style="display: none">--}}
{{--    <div class="popup__container">--}}
{{--        <div class="popup__heading">--}}
{{--            <div class="page-title oh">--}}
{{--                <span data-aos="fade-down" data-aos-duration="900">Прайс-лист</span>--}}
{{--            </div>--}}
{{--            <div class="popup__label">Укажите свой email, и мы пришлём Вам прайс-лист</div>--}}
{{--        </div>--}}
{{--        <form class="popup__form" action="{{ route('ajax.request-price') }}">--}}
{{--            <div class="popup__fields">--}}
{{--                <div class="field" data-aos="fade-down" data-aos-delay="350">--}}
{{--                    <input class="field__input" type="text" name="email" required>--}}
{{--                    <span class="field__highlight"></span>--}}
{{--                    <span class="field__bar"></span>--}}
{{--                    <label class="field__label">Email</label>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="popup__action">--}}
{{--                <button class="popup__submit btn-reset" aria-label="Отправить">--}}
{{--                    <span>Отправить</span>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </form>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<div class="popup" id="callback" style="display: none">--}}
{{--    <div class="popup__container">--}}
{{--        <div class="popup__heading">--}}
{{--            <div class="page-title oh">--}}
{{--                <span data-aos="fade-down" data-aos-duration="900">заказать звонок</span>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <form class="popup__form" action="{{ route('ajax.callback') }}">--}}
{{--            <div class="popup__fields">--}}
{{--                <div class="field" data-aos="fade-down" data-aos-delay="350">--}}
{{--                    <input class="field__input" type="text" name="name" required>--}}
{{--                    <span class="field__highlight"></span>--}}
{{--                    <span class="field__bar"></span>--}}
{{--                    <label class="field__label">Ваше имя</label>--}}
{{--                </div>--}}
{{--                <div class="field" data-aos="fade-down" data-aos-delay="350">--}}
{{--                    <input class="field__input" type="tel" name="phone" required>--}}
{{--                    <span class="field__highlight"></span>--}}
{{--                    <span class="field__bar"></span>--}}
{{--                    <label class="field__label">Телефон</label>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="popup__action">--}}
{{--                <button class="popup__submit btn-reset" aria-label="Отправить">--}}
{{--                    <span>Отправить</span>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </form>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<div class="popup" id="calc" style="display: none">--}}
{{--    <div class="popup__container">--}}
{{--        <div class="popup__heading">--}}
{{--            <div class="page-title oh">--}}
{{--                <span data-aos="fade-down" data-aos-duration="900">Расчёт цены</span>--}}
{{--            </div>--}}
{{--            <!-- сюда придёт название из кнопки data-label-->--}}
{{--            <div class="popup__label">Заявка на расчет цены</div>--}}
{{--        </div>--}}
{{--        <form class="popup__form" action="{{ route('ajax.calc') }}">--}}
{{--            <!-- сюда придёт название из кнопки data-label-->--}}
{{--            <input class="popup__name" type="hidden" name="destination">--}}
{{--            <div class="popup__fields">--}}
{{--                <div class="field" data-aos="fade-down" data-aos-delay="350">--}}
{{--                    <input class="field__input" type="text" name="name" required>--}}
{{--                    <span class="field__highlight"></span>--}}
{{--                    <span class="field__bar"></span>--}}
{{--                    <label class="field__label">Ваше имя</label>--}}
{{--                </div>--}}
{{--                <div class="field" data-aos="fade-down" data-aos-delay="350">--}}
{{--                    <input class="field__input" type="tel" name="phone" required>--}}
{{--                    <span class="field__highlight"></span>--}}
{{--                    <span class="field__bar"></span>--}}
{{--                    <label class="field__label">Телефон</label>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="popup__action">--}}
{{--                <button class="popup__submit btn-reset" aria-label="Отправить">--}}
{{--                    <span>Отправить</span>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </form>--}}
{{--    </div>--}}
{{--</div>--}}
{{--<div class="scrolltop" aria-label="В начало страницы" tabindex="1">--}}
{{--    <svg class="svg-sprite-icon icon-up">--}}
{{--        <use xlink:href="/static/images/sprite/symbol/sprite.svg#up"></use>--}}
{{--    </svg>--}}
{{--</div>--}}

<div class="popup" id="callback" style="display: none">
    <div class="popup__container">callback</div>
</div>
<div class="popup" id="request" style="display: none">
    <div class="popup__container">request</div>
</div>
<div class="popup" id="change-city" style="display: none">
    <div class="popup__container">change-city</div>
</div>
<div class="popup" id="subscribe" style="display: none">
    <div class="popup__container">subscribe</div>
</div>
<div class="popup" id="order" style="display: none">
    <div class="popup__container">
        <div class="popup__title">Товар добавлен в&nbsp;корзину</div>
        <div class="popup__label"></div>
        <div class="popup__actions">
            <a class="btn-cart btn-cart--wide" href="{{ route('cart') }}" title="Оформить заказ">
                <span class="btn-cart__label">Оформить заказ</span>
            </a>
            <button class="req-btn btn-reset" type="button" data-close-popup="" aria-label="Продолжить покупки">
                <span>Продолжить покупки</span>
            </button>
        </div>
    </div>
</div>
<div class="b-cookie" data-cookie="data-cookie">
    <div class="b-cookie__body">
        <div class="b-cookie__label">Находясь на example.ru, вы соглашаетесь с тем, что мы используем куки-файлы и принимаете условия
            <a href="{{ route('policy') }}">обработки перcональных данных</a>
        </div>
        <button class="b-cookie__close btn-reset" type="button" aria-label="Закрыть окно предупреждения">
            <span class="b-cookie__close-icon iconify" data-icon="mingcute:close-line" data-width="20"></span>
        </button>
    </div>
</div>
<div class="scrolltop" aria-label="В начало страницы" tabindex="1">
    <svg class="svg-sprite-icon icon-up" width="1em" height="1em">
        <use xlink:href="/static/images/sprite/symbol/sprite.svg#up"></use>
    </svg>
</div>