@extends('template')
@section('content')
    <main>
        <section class="s-newses">
            <div class="s-newses__container container">
                @include('blocks.bread')
                <div class="s-newses__heading">
                    <div class="page-title oh">
                        <span data-aos="fade-down" data-aos-duration="900">{{ $h1 }}</span>
                    </div>
                </div>
                @if(isset($items) && count($items))
                    <div class="s-reviews__slider swiper" data-reviews data-aos="fade-left" data-aos-delay="600">
                        <div class="s-reviews__wrapper swiper-wrapper">
                            @foreach($items as $item)
                                <div class="s-reviews__slide swiper-slide">
                                    <div class="s-reviews__name">Отзыв «{{ $item->name }}»</div>
                                    <div class="s-reviews__brand">
                                        <img class="s-reviews__pic lazy"
                                             src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                             data-src="{{ $item->image_src }}" width="198" height="198"
                                             alt="{{ $item->name }}">
                                    </div>
                                    <div class="s-reviews__text page-body">
                                        {!! $item->announce !!}
                                    </div>
                                    <div class="s-reviews__action">
                                        <a class="s-reviews__link" href="{{ $item->url }}"
                                           title="Отзыв {{ $item->name }}">
                                            <span>Читать отзыв</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </main>
@stop
