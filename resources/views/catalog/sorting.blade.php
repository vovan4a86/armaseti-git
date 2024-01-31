<div class="cat-view__sorting">
    <div class="b-sort">
        <div class="b-sort__title">Сортировать по цене:</div>
        <div class="b-sort__actions">
            <button class="b-sort__action {{ session('price_order', 'asc') == 'asc' ? 'is-active' : '' }} btn-reset"
                    type="button" aria-label="Сначала дешевые" data-price-order="asc">
                <span class="b-sort__action-label">Сначала дешевые</span>
                <span class="b-sort__action-icon iconify" data-icon="tabler:arrow-up"></span>
            </button>
            <button class="b-sort__action {{ session('price_order', 'asc') == 'desc' ? 'is-active' : '' }} btn-reset"
                    type="button" aria-label="Сначала дорогие" data-price-order="desc">
                <span class="b-sort__action-label">Сначала дорогие</span>
                <span class="b-sort__action-icon iconify" data-icon="tabler:arrow-down"></span>
            </button>
        </div>
    </div>
</div>
