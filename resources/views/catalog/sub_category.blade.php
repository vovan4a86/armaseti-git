@extends('template')
@section('content')
    <main>
        <section class="cards-view">
            <div class="cards-view__container container">
                @include('blocks.bread')
                <div class="cards-view__heading">
                    <div class="page-title oh">
                        <span data-aos="fade-down" data-aos-duration="900">{{ $h1 }}</span>
                    </div>
                </div>
                @if(isset($products) && count($products))
                    <div class="cards-view__grid">
                        @foreach($products as $item)
                            <div class="cards-view__item" data-aos="fade-down" data-aos-duration="900"
                                 data-aos-delay="150">
                                <div class="prod">
                                    <a href="{{ $item->url }}" title="{{ $item->name }}">
                                        @if($img = $item->images()->first())
                                            <img class="prod__pic lazy"
                                                 src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                                 data-src="{{ $img->thumb(2) }}" width="250" height="208"
                                                 alt=""/>
                                        @endif
                                    </a>
                                    <div class="prod__body">
                                        <a class="prod__title" href="{{ $item->url }}"
                                           title="{{ $item->name }}">{{ $item->name }}</a>
                                        <div class="prod__action">
                                            <button class="prod__btn btn-reset" data-request="data-request"
                                                    data-src="#calc"
                                                    data-label="{{ $item->name }}"
                                                    aria-label="Расчёт цены">
                                                <span class="prod__btn-icon lazy"
                                                      data-bg="/static/images/common/ico_calc.svg"></span>
                                                <span class="prod__btn-label">Расчёт цены</span>
                                                <svg width="86" height="19" viewBox="0 0 86 19" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M73.286 18L85.0001 9.5M85.0001 9.5L73.0001 0.999993M85.0001 9.5L6.02921e-05 9.5"
                                                          stroke="currentColor"/>
                                                </svg>

                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if($loop->iteration == 8)
                                <div class="cards-view__item cards-view__item--wide" data-aos="fade-down"
                                     data-aos-duration="900" data-aos-delay="550">
                                    <div class="b-action">
                                        <div class="b-action__title">Не нашли нужную коробку?</div>
                                        <div class="b-action__label">Сделаем на заказ!</div>
                                        <div class="b-action__action">
                                            <button class="b-action__btn btn-reset" type="button"
                                                    data-request="data-request" data-src="#calc"
                                                    data-label="Заказать бесплатный расчёт"
                                                    aria-label="заказать бесплатный расчёт">
                                                <span>заказать бесплатный расчёт</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        @include('blocks.features')
        @include('blocks.content_view')
    </main>
@endsection
