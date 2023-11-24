@extends('template')
@section('content')
    <main>
        <section class="hero">
            <div class="hero__promo lazy" data-bg="static/images/common/hero.jpg">
                @if($video = Settings::get('hero_video'))
                    <video class="hero__video" src="{{ Settings::fileSrc($video) }}" autoplay muted loop
                           playsinline></video>
                @endif
            </div>
            <div class="hero__container container">
                <div class="hero__body">
                    @if($titles = Settings::get('hero_titles'))
                        <div class="hero__brand">
                            <span data-aos="fade-down" data-aos-duration="1100"
                                  data-aos-delay="0">{{ $titles['top'] }}</span>
                        </div>
                        <div class="hero__actions">
                            <div class="hero__title">
                                <span data-aos="fade-up" data-aos-duration="900"
                                      data-aos-delay="300">{{ $titles['bottom'] }}</span>
                            </div>
                            <button class="hero__action btn-reset" type="button" data-popup data-src="#get-price" aria-label="запросить прайс-лист">
                                <span data-aos="flip-up" data-aos-duration="650" data-aos-delay="1600">запросить прайс-лист</span>
                                <svg width="86" height="19" viewBox="0 0 86 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M73.2859 18L85 9.5M85 9.5L73 0.999993M85 9.5L-7.43094e-07 9.5" stroke="currentColor" />
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
                <div class="hero__socials">
                    <div class="hero__socials-wrapper" data-aos="flip-up" data-aos-duration="650" data-aos-delay="1600">
                        <div class="social-links">
                            <div class="social-links__label">social media</div>
                            @if ($vk = Settings::get('soc_vk'))
                                <a class="social-links__icon" href="{{ $vk }}" title="Люкскрафт Youtube">
                                    <span class="iconify" data-icon="ant-design:youtube-filled"></span>
                                </a>
                            @endif
                            @if ($yt = Settings::get('soc_yt'))
                                <a class="social-links__icon" href="{{ $yt }}" title="Люкскрафт ВКонтакте">
                                    <span class="iconify" data-icon="ion:logo-vk"></span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="s-about">
            <img class="s-about__decor lazy"
                 src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                 data-src="static/images/common/s-about-decor.svg" width="320" height="690" alt="" data-aos="zoom-out"
                 data-aos-duration="1500" data-aos-delay="450">
            <div class="s-about__container container">
                <div class="s-about__grid">
                    <div class="s-about__company oh">
                        <div class="s-about__title">
                            <div class="page-title oh">
                                <span data-aos="fade-down" data-aos-duration="900">О компании</span>
                            </div>
                        </div>
                        <div class="s-about__text page-body" data-aos="fade-down" data-aos-duration="900"
                             data-aos-delay="50">
                            {!! Settings::get('main_about') !!}
                        </div>
                        <div class="s-about__link" data-aos="fade-down" data-aos-duration="900" data-aos-delay="70">
                            <a class="page-link" href="{{ url('about') }}" title="подробнее">
                                <span>подробнее</span>
                                <svg width="86" height="19" viewBox="0 0 86 19" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M73.286 18L85.0001 9.5M85.0001 9.5L73.0001 0.999993M85.0001 9.5L6.02921e-05 9.5"
                                          stroke="currentColor"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="s-about__content">
                        <div class="s-about__row">
                            @if ($img = Settings::get('main_about_img'))
                                <img class="s-about__pic lazy"
                                     src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                     data-src="{{ Settings::fileSrc($img) }}" width="486" height="725" alt="О компании"
                                     data-aos="flip-right" data-aos-duration="1000">
                            @endif
                            <div class="s-about__data">
                                <div class="about-data">
                                    <div class="about-data__subtitle">
                                        <span data-aos="fade-down" data-aos-duration="800" data-aos-delay="50">
                                            Почему мы?</span>
                                    </div>
                                    @if ($values = Settings::get('main_about_why'))
                                        <ul class="about-data__list list-reset oh">
                                            @foreach($values as $value)
                                                <li class="about-data__item" data-aos="fade-down"
                                                    data-aos-duration="800"
                                                    data-aos-delay="{{ $loop->index > 0 ? $loop->index * 50 : 0}}">
                                                    @if ($value['ico'])
                                                        <span class="about-data__icon lazy"
                                                              data-bg="{{ Settings::fileSrc($value['ico']) }}">
                                                        </span>
                                                    @endif
                                                    <span class="about-data__label">{{ $value['text'] }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @if ($main_catalog = Settings::get('main_catalog'))
            <section class="s-catalog">
                <div class="s-catalog__container container">
                    <div class="page-title oh">
                        <span data-aos="fade-down" data-aos-duration="900">Каталог продукции</span>
                    </div>
                    <div class="s-catalog__grid">
                        @foreach($main_catalog as $item)
                            <div class="s-catalog__item" data-aos="fade-up" data-aos-duration="800"
                                 data-aos-delay="{{ $loop->index > 0 ? $loop->index * 150 : 0}}">
                                <div class="s-catalog__view">
                                    <a href="{{ $city_alias !== null ? '/' . $city_alias . $item['url'] : $item['url'] }}" title="{{ $item['title'] }}">
                                        @if ($item['img'])
                                            <img class="s-catalog__pic lazy"
                                                 src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                                 data-src="{{ Settings::fileSrc($item['img']) }}" width="215"
                                                 height="215"
                                                 alt="{{ $item['title'] }}">
                                        @endif
                                    </a>
                                </div>
                                <div class="s-catalog__body">
                                    <div class="s-catalog__title">
                                        <a href="{{ $city_alias !== null ? '/' . $city_alias . $item['url'] : $item['url'] }}">{{ $item['title'] }}</a>
                                    </div>
                                    <div class="s-catalog__text">{{ $item['text'] }}</div>
                                    <div class="s-catalog__action">
                                        <a class="page-link" href="{{ $city_alias !== null ? '/' . $city_alias . $item['url'] : $item['url'] }}" title="Перейти">
                                            <span>Перейти</span>
                                            <svg width="86" height="19" viewBox="0 0 86 19" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path d="M73.286 18L85.0001 9.5M85.0001 9.5L73.0001 0.999993M85.0001 9.5L6.02921e-05 9.5"
                                                      stroke="currentColor"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
    @endif
    <!--class=(contactsPage && 'b-calc--small')-->
        <section class="b-calc {{ Route::is('contacts') ? 'b-calc--small' : ''}}">
            <div class="b-calc__container container">
                <div class="b-calc__body lazy" data-bg="static/images/common/b-calc-decor.svg" data-aos="flip-up">
                    <div class="b-calc__title" data-aos="fade-left" data-aos-delay="650">Получить расчёт цены</div>
                    <button class="b-calc__btn btn-reset" type="button" aria-label="Получить расчёт цены" data-popup
                            data-src="#calc" data-aos="fade-right" data-aos-delay="650">
                        <span>Получить</span>
                    </button>
                </div>
            </div>
        </section>
        <section class="s-reviews lazy" data-bg="static/images/common/reviews-decor.svg">
            <div class="s-reviews__container">
                <div class="s-reviews__body">
                    <div class="page-title oh">
                        <span data-aos="fade-down" data-aos-duration="900">Отзывы</span>
                    </div>
                    <div class="s-reviews__text page-body" data-aos="fade-down" data-aos-delay="500">
                        {!! Settings::get('main_review_text') !!}
                    </div>
                </div>
                @if (count($main_reviews))
                    <div class="s-reviews__slider swiper" data-reviews data-aos="fade-left" data-aos-delay="600">
                        <div class="s-reviews__wrapper swiper-wrapper">
                            @foreach($main_reviews as $item)
                                <div class="s-reviews__slide swiper-slide">
                                    <div class="s-reviews__name">Отзыв «{{ $item->name }}»</div>
                                    <div class="s-reviews__brand">
                                        <img class="s-reviews__pic lazy"
                                             src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                             data-src="{{ $item->image_src }}" width="198" height="198" alt="{{ $item->name }}">
                                    </div>
                                    <div class="s-reviews__text page-body">
                                        {!! $item->announce !!}
                                    </div>
                                    <div class="s-reviews__action">
                                        <a class="s-reviews__link" href="{{ $item->url }}" title="Отзыв {{ $item->name }}">
                                            <span>Читать отзыв</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <button class="s-reviews__next btn-reset" type="button" data-review-next
                            aria-label="следующий отзыв">
                        <svg width="86" height="19" viewBox="0 0 86 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M73.2859 17.873L85 9.37304M85 9.37304L73 0.87304M85 9.37304L-7.43094e-07 9.37305"
                                  stroke="currentColor"/>
                        </svg>
                    </button>
                @endif
            </div>
        </section>
        <section class="s-map lazy" data-bg="static/images/common/map.png">
            <div class="s-map__container container">
                <div class="s-map__body">
                    <div class="page-title oh">
                        <span data-aos="fade-down" data-aos-duration="900">{{ Settings::get('main_geo_title') }}</span>
                    </div>
                    <div class="s-map__text page-body" data-aos="fade-down" data-aos-delay="400">
                        <p>{{ Settings::get('main_geo_text') }}</p>
                    </div>
                    @if($feat = Settings::get('main_geo_feat'))
                        <div class="s-map__data" data-aos="fade-down" data-aos-delay="450">
                            @foreach($feat as $item)
                                <div class="s-map__data-item">
                                    @if($item['icon'])
                                        <div class="s-map__data-icon lazy"
                                             data-bg="{{ Settings::fileSrc($item['icon']) }}"></div>
                                    @endif
                                    <div class="s-map__data-body">{{ $item['text'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
        <section class="s-clients lazy" data-bg="static/images/common/clients-bg.svg">
            <div class="s-clients__container container">
                <div class="s-clients__grid">
                    @if($img = Settings::get('main_clients_img'))
                        <div class="s-clients__view lazy" data-bg="{{ Settings::fileSrc($img) }}"></div>
                    @endif
                    <div class="s-clients__body">
                        <div class="s-clients__head">
                            <div class="page-title oh">
                                <span data-aos="fade-down" data-aos-duration="900">Наши клиенты</span>
                            </div>
                            <a class="s-clients__link" href="{{ url('partners') }}" title="Наши клиенты">
                                <svg width="86" height="19" viewBox="0 0 86 19" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M73.286 18L85.0001 9.5M85.0001 9.5L73.0001 0.999993M85.0001 9.5L6.02921e-05 9.5"
                                          stroke="currentColor"/>
                                </svg>
                            </a>
                            @if($clients = Settings::get('main_clients'))
                                <div class="s-clients__list" data-aos="fade-down" data-aos-delay="500">
                                    @foreach($clients as $client)
                                        <div class="s-clients__preview">
                                            @if($client['image'])
                                                <img class="s-clients__pic lazy"
                                                     src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                                     data-src="{{ Settings::fileSrc($client['image']) }}" width="145"
                                                     height="120"
                                                     title="{{ $client['name'] }}"
                                                     alt="{{ $client['name'] }}">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('blocks.callback')
    </main>
@stop
