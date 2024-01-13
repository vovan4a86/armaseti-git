@extends('template')
@section('content')
    {{--    @include('blocks.bread')--}}
    <main>
        <section class="success">
            <div class="success__container container">
                <div class="success__grid">
                    <div class="success__body">
                        <div class="success__title">Ваша заявка успешно отправлена!</div>
                        <div class="success__text">В ближайшем времени с вами свяжется наш менеджер для уточнения
                            деталей.
                        </div>
                        <a class="success__link btn btn--accent btn--wide" href="javascript:void(0)">
                            <span>Вернуться на главную</span>
                        </a>
                    </div>
                    <div class="success__decor no-select">
                        <img class="decor-img lazy"
                             src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                             data-src="static/images/common/success.png" width="527" height="329" alt="">
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
