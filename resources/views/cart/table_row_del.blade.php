<div class="b-cart__item" data-id="{{ $item['id'] }}">
    <div class="del-item">
        <div class="del-item__data">
            <div class="del-item__model">{{ $item['name'] }}</div>
            <div class="del-item__price">{{ number_format($item['price'] * $item['count'], 0, ',', ' ') }} ₽</div>
        </div>
        <div class="del-item__actions">
            <div class="del-item__label">Данный товар удалён</div>
            <button class="del-item__action btn-reset"
                    type="button" arial-label="Восстановить товар">
                Восстановить?
            </button>
        </div>
    </div>
</div>