@extends('template')
@section('content')
    @include('blocks.bread')
    <main>
        <section class="page container">
            <div class="page__title">{{ $h1 }}</div>
            <form class="b-cart" action="#">
                <div class="b-cart__dialog">
                    <div class="b-dialog">
                        <div class="b-dialog__icons">
                            <div class="b-dialog__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m8.041 14.065 6.404-6.405" />
                                    <circle cx="13.804" cy="13.424" r=".85" fill="currentColor" stroke="currentColor" stroke-width=".435" />
                                    <circle cx="8.681" cy="8.728" r=".85" fill="currentColor" stroke="currentColor" stroke-width=".435" />
                                    <path stroke="currentColor" stroke-width="1.7" d="m10.311 20.37-.272-.805.272.805a2.15 2.15 0 0 1 1.378 0l.272-.805-.272.805.183.062a3.85 3.85 0 0 0 4.523-1.646l.1-.165a2.15 2.15 0 0 1 1.056-.886l.18-.07a3.85 3.85 0 0 0 2.407-4.168l-.03-.191a2.15 2.15 0 0 1 .24-1.357l.092-.17a3.85 3.85 0 0 0-.835-4.74l-.146-.128a2.15 2.15 0 0 1-.689-1.193l-.833.167.833-.167-.037-.19a3.85 3.85 0 0 0-3.688-3.093l-.193-.005a2.15 2.15 0 0 1-1.295-.47l-.53.663.53-.664-.15-.12a3.85 3.85 0 0 0-4.814 0l-.15.12a2.15 2.15 0 0 1-1.295.471l-.193.005a3.85 3.85 0 0 0-3.688 3.094l-.038.19a2.15 2.15 0 0 1-.688 1.192l-.146.128.562.639-.562-.639a3.85 3.85 0 0 0-.835 4.74l.092.17.735-.402-.735.402c.227.414.31.89.24 1.357l-.03.19a3.85 3.85 0 0 0 2.407 4.169l.18.07c.44.172.81.483 1.056.886l.1.165.726-.442-.726.442a3.85 3.85 0 0 0 4.523 1.646l.183-.062Z"
                                    />
                                </svg>
                            </div>
                            <div class="b-dialog__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.5" d="M11 21C5.486 21 1 16.514 1 11S5.486 1 11 1s10 4.486 10 10-4.486 10-10 10Z" />
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.5" d="M9.431 11.102H8.143m1.288 0V7.429h3.222c2.56 0 2.556 3.673 0 3.673H9.431Zm0 0V16m-1.288-2.449h5.154" />
                                </svg>
                            </div>
                        </div>
                        <div class="b-dialog__body">Скидка на объём составляет до 30%, финальная цена рассчитывается менеджером и сообщается по предпочтительным средствам связи</div>
                    </div>
                </div>
                <div class="b-cart__list">
                    @if(count($items))
                        @foreach($items as $item)
                            @include('cart.table_row')
                        @endforeach
                    @else
                        <div>Пустая корзина</div>
                    @endif
                </div>
                <div class="b-cart__sum">
                    <div class="b-cart__sum-label">Общая сумма заказа</div>
                    <div class="b-cart__sum-data">2 227 484 ₽</div>
                </div>
                @include('cart.cart_order')
            </form>
        </section>
    </main>
@endsection
