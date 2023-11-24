@extends('template')
@section('content')
    <main>
        <div class="container">
            <div class="error-page">
                <div class="error-page__title">Страница не найдена</div>
                <div class="error-page__view lazy entered loaded" data-bg="/static/images/common/404.svg" data-ll-status="loaded" style="background-image: url(&quot;/static/images/common/404.svg&quot;);"></div>
                <div class="error-page__text">Запрашиваемая страница не найдена. Возможно вы сделали опечатку в адресе или страница была перемещена</div>
                <div class="error-page__action">
                    <a class="btn btn--primary" href="{{ route('main') }}">
                        <span>На главную</span>
                    </a>
                </div>
            </div>
        </div>
    </main>
@stop
