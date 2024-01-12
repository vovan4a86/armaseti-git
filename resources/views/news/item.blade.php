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

                                    @if(count($item->images))
                                        <div class="text-block__gallery">
                                            @foreach($item->images as $image)
                                                <a class="text-block__gallery-item" href="{{ $image->imageSrc() }}"
                                                   data-fancybox="gallery" data-caption="{{ $item->name }}">
                                                    <img class="text-block__pic" src="{{ $image->thumb(2) }}"
                                                         width="116" height="186" alt="{{ $item->name }}" loading="lazy">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

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
                @include('news.aside')
            </div>
        </div>
    </div>
@stop
