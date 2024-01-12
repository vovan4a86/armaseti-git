@php $landingPage = true @endphp
@extends('template')
@section('content')
    @include('blocks.bread')
    <div class="news-layout">
        <div class="news-layout__container container">
            <div class="news-layout__grid">
                <!--main._main-->
                <main class="news-layout__main">
                    <section class="page">
                        <div class="page__title">{{ $h1 }}</div>
                        <div class="page__content">
                            @if($item->image)
                                <div class="page__head">
                                    <img class="page__cover" src="{{ $item->thumb(3) }}" width="790" height="360"
                                         alt="{{ $item->name }}">
                                </div>
                            @endif
                            <div class="page__meta">
                                <div class="page__date">{{ $item->dateFormat() }}</div>
                                <div class="page__badge">
                                    @if($item->type == 'Новость')
                                        <div class="badge badge--grey-alt">Новость</div>
                                    @elseif($item->type == 'Статья')
                                        <div class="badge badge--accent-alt">Статья</div>
                                    @else
                                        <div class="badge badge--accent">Акция</div>
                                    @endif
                                </div>
                            </div>
                            <div class="page__body">
                                <div class="text-block">
                                    {!! $text !!}

                                    <div class="text-block__gallery">
                                        <a class="text-block__gallery-item" href="/static/images/common/gal-1.png"
                                           data-fancybox="gallery" data-caption="caption">
                                            <img class="text-block__pic" src="/static/images/common/gal-1.png"
                                                 width="116" height="186" alt="name" loading="lazy">
                                        </a>
                                        <a class="text-block__gallery-item" href="/static/images/common/gal-2.png"
                                           data-fancybox="gallery" data-caption="caption">
                                            <img class="text-block__pic" src="/static/images/common/gal-2.png"
                                                 width="116" height="186" alt="name" loading="lazy">
                                        </a>
                                        <a class="text-block__gallery-item" href="/static/images/common/gal-3.png"
                                           data-fancybox="gallery" data-caption="caption">
                                            <img class="text-block__pic" src="/static/images/common/gal-3.png"
                                                 width="116" height="186" alt="name" loading="lazy">
                                        </a>
                                    </div>

                                   {!! $text_after !!}
                                </div>
                                <div class="page__back">
                                    <a class="back-link" href="{{ route('news') }}" title="Вернуться к ленте новостей">
                                        <span class="back-link__icon iconify" data-icon="jam:arrow-left"
                                              data-width="17"></span>
                                        <span class="back-link__label">Вернуться к ленте новостей</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>
                <!--aside._aside-->
                <aside class="news-layout__aside">
                    <div class="a-newses">
                        <div class="a-newses__item">
                            <div class="a-newses__top">
                                <div class="a-newses__badge">
                                    <!-- modificators: --accent --accent-alt --alt --grey-alt -->
                                    <div class="badge badge--accent">Акция</div>
                                </div>
                                <div class="a-newses__date">01.09.2023</div>
                            </div>
                            <div class="a-newses__body">
                                <a class="a-newses__title" href="javascript:void(0)">ГК Армасети открывает новый склад в
                                    г. Екатеринбурге!</a>
                                <div class="a-newses__text">Задвижка — это одна из разновидностей запорной арматуры,
                                    которая перекрывала
                                </div>
                            </div>
                            <div class="a-newses__foot">
                                <a class="read-link" href="javascript:void(0)" title="Читать">
                                    <span class="read-link__label">Читать</span>
                                    <span class="read-link__icon iconify" data-icon="jam:arrow-right"
                                          data-width="17"></span>
                                </a>
                            </div>
                        </div>
                        <div class="a-newses__item">
                            <div class="a-newses__top">
                                <div class="a-newses__badge">
                                    <!-- modificators: --accent --accent-alt --alt --grey-alt -->
                                    <div class="badge badge--alt">Новость</div>
                                </div>
                                <div class="a-newses__date">01.09.2023</div>
                            </div>
                            <div class="a-newses__body">
                                <a class="a-newses__title" href="javascript:void(0)">ГК Армасети открывает новый склад в
                                    г. Екатеринбурге!</a>
                                <div class="a-newses__text">Задвижка — это одна из разновидностей запорной арматуры,
                                    которая перекрывала
                                </div>
                            </div>
                            <div class="a-newses__foot">
                                <a class="read-link" href="javascript:void(0)" title="Читать">
                                    <span class="read-link__label">Читать</span>
                                    <span class="read-link__icon iconify" data-icon="jam:arrow-right"
                                          data-width="17"></span>
                                </a>
                            </div>
                        </div>
                        <div class="a-newses__item">
                            <div class="a-newses__top">
                                <div class="a-newses__badge">
                                    <!-- modificators: --accent --accent-alt --alt --grey-alt -->
                                    <div class="badge badge--accent-alt">Статья</div>
                                </div>
                                <div class="a-newses__date">01.09.2023</div>
                            </div>
                            <div class="a-newses__body">
                                <a class="a-newses__title" href="javascript:void(0)">ГК Армасети открывает новый склад в
                                    г. Екатеринбурге!</a>
                                <div class="a-newses__text">Задвижка — это одна из разновидностей запорной арматуры,
                                    которая перекрывала
                                </div>
                            </div>
                            <div class="a-newses__foot">
                                <a class="read-link" href="javascript:void(0)" title="Читать">
                                    <span class="read-link__label">Читать</span>
                                    <span class="read-link__icon iconify" data-icon="jam:arrow-right"
                                          data-width="17"></span>
                                </a>
                            </div>
                        </div>
                        <div class="a-newses__item">
                            <div class="a-newses__top">
                                <div class="a-newses__badge">
                                    <!-- modificators: --accent --accent-alt --alt --grey-alt -->
                                    <div class="badge badge--accent-alt">Статья</div>
                                </div>
                                <div class="a-newses__date">01.09.2023</div>
                            </div>
                            <div class="a-newses__body">
                                <a class="a-newses__title" href="javascript:void(0)">ГК Армасети открывает новый склад в
                                    г. Екатеринбурге!</a>
                                <div class="a-newses__text">Задвижка — это одна из разновидностей запорной арматуры,
                                    которая перекрывала
                                </div>
                            </div>
                            <div class="a-newses__foot">
                                <a class="read-link" href="javascript:void(0)" title="Читать">
                                    <span class="read-link__label">Читать</span>
                                    <span class="read-link__icon iconify" data-icon="jam:arrow-right"
                                          data-width="17"></span>
                                </a>
                            </div>
                        </div>
                        <div class="a-newses__item">
                            <div class="a-newses__top">
                                <div class="a-newses__badge">
                                    <!-- modificators: --accent --accent-alt --alt --grey-alt -->
                                    <div class="badge badge--alt">Новость</div>
                                </div>
                                <div class="a-newses__date">01.09.2023</div>
                            </div>
                            <div class="a-newses__body">
                                <a class="a-newses__title" href="javascript:void(0)">ГК Армасети открывает новый склад в
                                    г. Екатеринбурге!</a>
                                <div class="a-newses__text">Задвижка — это одна из разновидностей запорной арматуры,
                                    которая перекрывала
                                </div>
                            </div>
                            <div class="a-newses__foot">
                                <a class="read-link" href="javascript:void(0)" title="Читать">
                                    <span class="read-link__label">Читать</span>
                                    <span class="read-link__icon iconify" data-icon="jam:arrow-right"
                                          data-width="17"></span>
                                </a>
                            </div>
                        </div>
                        <div class="a-newses__item">
                            <div class="a-newses__top">
                                <div class="a-newses__badge">
                                    <!-- modificators: --accent --accent-alt --alt --grey-alt -->
                                    <div class="badge badge--accent">Акция</div>
                                </div>
                                <div class="a-newses__date">01.09.2023</div>
                            </div>
                            <div class="a-newses__body">
                                <a class="a-newses__title" href="javascript:void(0)">ГК Армасети открывает новый склад в
                                    г. Екатеринбурге!</a>
                                <div class="a-newses__text">Задвижка — это одна из разновидностей запорной арматуры,
                                    которая перекрывала
                                </div>
                            </div>
                            <div class="a-newses__foot">
                                <a class="read-link" href="javascript:void(0)" title="Читать">
                                    <span class="read-link__label">Читать</span>
                                    <span class="read-link__icon iconify" data-icon="jam:arrow-right"
                                          data-width="17"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@stop
