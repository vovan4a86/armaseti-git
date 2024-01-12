@extends('template')
@section('content')
    @include('blocks.bread')
    <!--.layout-->
    <div class="layout">
        <div class="layout__container container">
            <!--aside._aside-->
            @include('catalog.aside_filter')
            <!--main._main-->
            <main class="layout__main">
                <div class="page">
                    <!--mobile filter-->
                    @include('catalog.mobile_filter')
                    <!--._item-->
                    @if(count($category->public_children))
                        <div class="page__item">
                            <div class="b-nav">
                                <nav class="b-nav__nav">
                                    <ul class="b-nav__list list-reset">
                                        @foreach($category->public_children as $category)
                                            <li class="b-nav__item">
                                                <a class="b-nav__link" href="{{ $category->url }}" data-link="data-link">{{ $category->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    @endif
                    <!--._item-->
                    <div class="page__item">
                        <!--.cat-view(x-data="{ listView: false }")-->
                        <div class="cat-view" x-data="{ listView: false }">
                            <div class="cat-view__body">
                                <div class="cat-view__actions">
                                    @include('catalog.sorting')
                                    @include('catalog.toggles')
                                </div>
                                <!--._list(:class="listView && 'is-active'")-->
                                @if(count($products))
                                    <div class="cat-view__list" :class="listView &amp;&amp; 'is-active'">
                                        @include('catalog.product_item')
                                    </div>
                                @endif
                            </div>
                            <!--._load-->
                            <div class="cat-view__load">
                                <button class="b-loader btn-reset" type="button" aria-label="Загрузить еще">
                                    <span>Загрузить еще</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="18" fill="none">
                                        <path fill="currentColor" stroke="currentColor" stroke-width=".7" d="M8.28 17a7.281 7.281 0 0 0 2.694-14.045l1.943-1.23a.393.393 0 0 0-.421-.664L9.867 2.723l-.012.008-.004.003c-.002 0-.003.002-.005.003a.395.395 0 0 0-.022.016l-.01.007a.388.388 0 0 0-.023.02l-.01.01a.417.417 0 0 0-.014.016l-.007.007a.385.385 0 0 0-.02.026l-.008.013a.375.375 0 0 0-.008.013l-.007.011a.388.388 0 0 0-.014.028l-.007.018-.002.006-.008.02c-.001.006-.004.01-.006.016 0 .005 0 .009-.002.013l-.002.017L9.675 3c-.002.009-.004.017-.004.026l-.003.028v.012l.001.013.001.024c0 .01.001.018.003.027l.003.015.003.012.003.016.787 2.811a.394.394 0 0 0 .758-.212l-.588-2.102a6.493 6.493 0 1 1-4.036-.223.393.393 0 0 0-.203-.76A7.28 7.28 0 0 0 8.28 17Z"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <div class="cat-view__pagination">
                                <div class="b-pagination">
                                    <a class="b-pagination__link b-pagination__link--btn is-disabled" href="javascript:void(0)" title="Назад">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M11.459 6.5H1.542m0 0L6.5 11.458M1.542 6.5 6.5 1.542" />
                                        </svg>
                                    </a>
                                    <div class="b-pagination__pages">
                                        <a class="b-pagination__link is-active" href="javascript:void(0)" title="Страница 1">1</a>
                                        <a class="b-pagination__link" href="javascript:void(0)" title="Страница 2">2</a>
                                        <a class="b-pagination__link" href="javascript:void(0)" title="Страница 3">3</a>
                                        <a class="b-pagination__link" href="javascript:void(0)" title="Страница 4">4</a>
                                    </div>
                                    <a class="b-pagination__link b-pagination__link--btn" href="javascript:void(0)" title="Дальше">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M1.542 6.5h9.917m0 0-4.958 4.958M11.459 6.5 6.501 1.542" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--._item.-text-->
                    <div class="page__item page__item--text">
                        <div class="text-block">

                            {!! $text !!}

                            <div class="text-block__gallery">
                                <a class="text-block__gallery-item" href="/static/images/common/gal-1.png" data-fancybox="gallery" data-caption="caption">
                                    <img class="text-block__pic" src="/static/images/common/gal-1.png" width="116" height="186" alt="name" loading="lazy">
                                </a>
                                <a class="text-block__gallery-item" href="/static/images/common/gal-2.png" data-fancybox="gallery" data-caption="caption">
                                    <img class="text-block__pic" src="/static/images/common/gal-2.png" width="116" height="186" alt="name" loading="lazy">
                                </a>
                                <a class="text-block__gallery-item" href="/static/images/common/gal-3.png" data-fancybox="gallery" data-caption="caption">
                                    <img class="text-block__pic" src="/static/images/common/gal-3.png" width="116" height="186" alt="name" loading="lazy">
                                </a>
                            </div>

                            <p>Арматура может управляться вручную, а&nbsp;также задвижка может быть оснащена пневмоприводом или электроприводом до&nbsp;380В, например&nbsp;&mdash;
                                <a href="#">Задвижка шиберная ножевая DN.ru PN16&nbsp;с электроприводом DN.ru 380В</a>. Монтаж производится на&nbsp;горизонтальных и&nbsp;вертикальных трубопроводах. Различают муфтовые, флан...</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!--section.s-req-->
    @include('blocks.request')
@endsection
