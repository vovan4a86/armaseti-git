<div class="page__filter">
    <div class="filter-view" x-data="{ filterIsOpen: false }">
        <div class="filter-view__action">
            <button class="filter-view__btn btn-reset" type="button" aria-label="Подбор оборудования"
                    @click="filterIsOpen = true">
                <span class="filter-view__btn-label">Подбор оборудования</span>
                <span class="filter-view__btn-icon iconify" data-icon="fluent:filter-32-filled"></span>
            </button>
        </div>
        <div class="filter-view__aside" :class="filterIsOpen &amp;&amp; 'is-active'">
            <!--form.b-filter-->
            @include('catalog.b_filter')
            <button class="filter-view__close btn-reset" type="button"
                    aria-label="Закрыть подбор" @click="filterIsOpen = false">
                <span class="iconify" data-icon="carbon:close-filled" data-width="24"></span>
            </button>
        </div>
        <div class="filter-view__aside filter-view__aside--backdrop"
             :class="filterIsOpen &amp;&amp; 'is-active'" @click="filterIsOpen = false"></div>
    </div>
</div>
