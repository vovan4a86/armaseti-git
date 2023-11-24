@extends('template')
@section('content')
    <main>
        <section class="product">
            <div class="product__container container">
                @include('blocks.bread')
                <div class="product__body" data-aos="fade-up" data-aos-duration="600" data-aos-delay="150">
                    <div class="product__views">
                        <div class="product__nav">
                            <button class="product__arrow product__arrow--prev btn-reset" type="button" aria-label="Предыдущий слайд" data-product-prev="data-product-prev">
                                <svg xmlns="http://www.w3.org/2000/svg" width="27" height="12" fill="none">
                                    <path fill="currentColor" d="M26.29 10.98 13.94.18a.614.614 0 0 0-.456-.18.614.614 0 0 0-.455.18L.68 10.98a.526.526 0 0 0-.195.42c0 .36.26.6.65.6.195 0 .325-.06.455-.18L13.484 1.44 25.38 11.82c.13.12.26.18.455.18.39 0 .65-.24.65-.6 0-.18-.065-.3-.195-.42Z"
                                    />
                                </svg>
                            </button>
                            @if(count($images))
                            <div class="product__slider-nav swiper" data-product-slider-nav="data-product-slider-nav">
                                <div class="product__wrapper product__wrapper--nav swiper-wrapper">
                                    @foreach($images as $img)
                                        <div class="product__slide-nav swiper-slide">
                                            <img class="product__thumb" src="{{ $img->thumb(3) }}" width="135" height="90" alt="{{ $product->name }}" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            <button class="product__arrow product__arrow--next btn-reset" type="button" aria-label="Следующий слайд" data-product-next="data-product-next">
                                <svg xmlns="http://www.w3.org/2000/svg" width="27" height="13" fill="none">
                                    <path fill="currentColor" d="m26.29 1.495-12.35 10.8c-.13.12-.26.18-.456.18a.614.614 0 0 1-.455-.18L.68 1.495a.526.526 0 0 1-.195-.42c0-.36.26-.6.65-.6.195 0 .325.06.455.18l11.895 10.38L25.38.655c.13-.12.26-.18.455-.18.39 0 .65.24.65.6 0 .18-.065.3-.195.42Z"
                                    />
                                </svg>
                            </button>
                        </div>
                        @if(count($images))
                        <div class="product__view swiper" data-product-slider="data-product-slider">
                            <div class="product__wrapper product__wrapper--product swiper-wrapper">
                                @foreach($images as $img)
                                <div class="product__slide-prod swiper-slide">
                                    <a class="product__link" href="{{ $img->image_src }}" data-fancybox="data-fancybox" data-caption="{{ $product->name }}">
                                        <img class="product__picture" src="{{ $img->thumb(4) }}" width="616" height="441" alt="{{ $product->name }}" />
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="product__info">
                        <div class="product__title">{{ $product->name }}</div>
                        <div class="product__instock">{{ $product->in_stock ? 'в наличии' : 'нет в наличии' }}</div>
                        <div class="product__params">
                            <div class="product__params-title">Внутренний размер ящиков</div>
                            <div class="product__params-grid">
                                <div class="product__params-item">
                                    <div class="product__params-icon lazy" data-bg="/static/images/common/ico_length.svg"></div>
                                    <div class="product__params-label">Длина {{ $product->length ? $product->length . ' мм.' : '-' }}</div>
                                </div>
                                <div class="product__params-item">
                                    <div class="product__params-icon lazy" data-bg="/static/images/common/ico_width.svg"></div>
                                    <div class="product__params-label">Ширина {{ $product->width ? $product->width . ' мм.' : '-' }}</div>
                                </div>
                                <div class="product__params-item">
                                    <div class="product__params-icon lazy" data-bg="/static/images/common/ico_height.svg"></div>
                                    <div class="product__params-label">Высота {{ $product->height ? $product->height . ' мм.' : '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="product__actions">
                            <div class="product__price">{{ $product->price ? $product->price_format . ' ₽' : 'нет цены' }}</div>
                            <button class="product__action btn-reset" type="button">
                                <span>выбрать</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('catalog.related')
        @include('blocks.features')
        @include('blocks.content_view')
    </main>
@endsection
