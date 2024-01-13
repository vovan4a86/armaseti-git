<div class="header__action" data-header-cart>
    <a class="h-action" href="{{ route('cart') }}" title="Корзина">
        <span class="icon iconify" data-icon="mynaui:cart" data-width="22"></span>
        @if(\Fanky\Admin\Cart::count())
            <span class="h-action__counter">{{ \Fanky\Admin\Cart::count() }}</span>
        @endif
    </a>
</div>