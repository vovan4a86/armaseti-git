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
            <a href="{{ route('policy') }}">обработки персональных данных</a>
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