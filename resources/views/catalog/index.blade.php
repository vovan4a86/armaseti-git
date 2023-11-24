@extends('template')
@section('content')
    <main>
        <section class="catalog-view">
            <div class="catalog-view__container container">
                @include('blocks.bread')
                <div class="catalog-view__heading">
                    <div class="page-title oh">
                        <span data-aos="fade-down" data-aos-duration="900">{{ $h1 }}</span>
                    </div>
                </div>
                @if(isset($categories) && count($categories))
                    <div class="catalog-view__grid">
                        @foreach($categories as $category)
                            <div class="catalog-view__item" data-aos="fade-down" data-aos-duration="900"
                             data-aos-delay="{{ $loop->index > 0 ? $loop->index * 50 + 150 : 150}}">
                            <div class="card">
                                @if($category->image)
                                <img class="card__pic lazy"
                                     src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                     data-src="{{ $category->thumb(2) }}" width="250" height="208" alt="{{ $category->name }}"/>
                                @endif
                                <div class="card__body">
                                    <a class="card__title" href="{{ $category->url }}"
                                       title="{{ $category->name }}">{{ $category->name }}</a>
                                    <div class="card__txt">
                                        {!! $category->announce !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        @include('blocks.features')
        <section class="content-view">
            <!-- обёртка для спойлера в контенте-->
            <!-- высота по-умолчанию, вынесена inline, чтобы можно было добавить поле в админке (может быть любым)-->
            <!-- (style="height: 800px")-->
            <div class="content-view__container container container--small js-hide_container" data-aos="fade-down"
                 data-aos-duration="1200">
                <div class="text-block js-hide_container__inn" style="height: 800px">
                    {!! $text !!}
                </div>
                <button class="btn-reset js-hide_container__btn" type="button" aria-label="показать/скрыть текст">
                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="none">
                        <circle cx="30" cy="30" r="30" fill="#8A847F"/>
                        <path fill="#fff"
                              d="M42.35 25a.59.59 0 0 0-.455.195L30 36.443 18.105 25.195A.59.59 0 0 0 17.65 25c-.39 0-.65.26-.65.65a.59.59 0 0 0 .195.455l12.35 11.704c.13.13.26.195.455.195a.59.59 0 0 0 .455-.195l12.35-11.704A.59.59 0 0 0 43 25.65c0-.39-.26-.65-.65-.65Z"
                        />
                    </svg>
                </button>
            </div>
        </section>
    </main>
@endsection
