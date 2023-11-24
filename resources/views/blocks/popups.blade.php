<div class="popup" id="is-done" style="display: none">
    <div class="popup__container">
        <div class="popup__heading">
            <div class="page-title oh">
                <span data-aos="fade-down" data-aos-duration="900" data-custom-title="data-custom-title">Отправлено!</span>
            </div>
            <div class="popup__label" data-custom-label>Отлично, в течении 15 минут, мы с вами свяжемся</div>
        </div>
    </div>
</div>
<div class="popup" id="get-price" style="display: none">
    <div class="popup__container">
        <div class="popup__heading">
            <div class="page-title oh">
                <span data-aos="fade-down" data-aos-duration="900">Прайс-лист</span>
            </div>
            <div class="popup__label">Укажите свой email, и мы пришлём Вам прайс-лист</div>
        </div>
        <form class="popup__form" action="{{ route('ajax.request-price') }}">
            <div class="popup__fields">
                <div class="field" data-aos="fade-down" data-aos-delay="350">
                    <input class="field__input" type="text" name="email" required>
                    <span class="field__highlight"></span>
                    <span class="field__bar"></span>
                    <label class="field__label">Email</label>
                </div>
            </div>
            <div class="popup__action">
                <button class="popup__submit btn-reset" aria-label="Отправить">
                    <span>Отправить</span>
                </button>
            </div>
        </form>
    </div>
</div>
<div class="popup" id="callback" style="display: none">
    <div class="popup__container">
        <div class="popup__heading">
            <div class="page-title oh">
                <span data-aos="fade-down" data-aos-duration="900">заказать звонок</span>
            </div>
        </div>
        <form class="popup__form" action="{{ route('ajax.callback') }}">
            <div class="popup__fields">
                <div class="field" data-aos="fade-down" data-aos-delay="350">
                    <input class="field__input" type="text" name="name" required>
                    <span class="field__highlight"></span>
                    <span class="field__bar"></span>
                    <label class="field__label">Ваше имя</label>
                </div>
                <div class="field" data-aos="fade-down" data-aos-delay="350">
                    <input class="field__input" type="tel" name="phone" required>
                    <span class="field__highlight"></span>
                    <span class="field__bar"></span>
                    <label class="field__label">Телефон</label>
                </div>
            </div>
            <div class="popup__action">
                <button class="popup__submit btn-reset" aria-label="Отправить">
                    <span>Отправить</span>
                </button>
            </div>
        </form>
    </div>
</div>
<div class="popup" id="calc" style="display: none">
    <div class="popup__container">
        <div class="popup__heading">
            <div class="page-title oh">
                <span data-aos="fade-down" data-aos-duration="900">Расчёт цены</span>
            </div>
            <!-- сюда придёт название из кнопки data-label-->
            <div class="popup__label">Заявка на расчет цены</div>
        </div>
        <form class="popup__form" action="{{ route('ajax.calc') }}">
            <!-- сюда придёт название из кнопки data-label-->
            <input class="popup__name" type="hidden" name="destination">
            <div class="popup__fields">
                <div class="field" data-aos="fade-down" data-aos-delay="350">
                    <input class="field__input" type="text" name="name" required>
                    <span class="field__highlight"></span>
                    <span class="field__bar"></span>
                    <label class="field__label">Ваше имя</label>
                </div>
                <div class="field" data-aos="fade-down" data-aos-delay="350">
                    <input class="field__input" type="tel" name="phone" required>
                    <span class="field__highlight"></span>
                    <span class="field__bar"></span>
                    <label class="field__label">Телефон</label>
                </div>
            </div>
            <div class="popup__action">
                <button class="popup__submit btn-reset" aria-label="Отправить">
                    <span>Отправить</span>
                </button>
            </div>
        </form>
    </div>
</div>
<div class="scrolltop" aria-label="В начало страницы" tabindex="1">
    <svg class="svg-sprite-icon icon-up">
        <use xlink:href="/static/images/sprite/symbol/sprite.svg#up"></use>
    </svg>
</div>
