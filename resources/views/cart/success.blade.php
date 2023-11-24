@extends('template')
@section('content')
{{--    @include('blocks.bread')--}}
    <main>
        <!-- homepage ? '' : 'section--inner'-->
        <section class="cart-blank section {{ Request::url() === '/' ? '' : 'section--inner' }}">
            <div class="cart-blank__container container">
                <img class="cart-blank__picture lazy" src="/" data-src="/static/images/common/cart.svg" alt="alt" width="165" height="165">
                <h2 class="cart-blank__title">Ваш заказ N{{ $id }} оформлен, в ближайшее время наши сотрудники свяжутся с вами.</h2>
                <p class="cart-blank__text">
                    <a href="{{ route('main') }}">Перейти на главную</a>
                </p>
            </div>
        </section>
    </main>
@endsection
