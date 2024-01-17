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
                                                <a class="b-nav__link {{ $category->isActive ? 'is-active' : '' }}"
                                                   href="{{ $category->url }}" data-link="data-link">{{ $category->name }}</a>
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
                                        @foreach($products as $product)
                                            @include('catalog.product_item_catalog')
                                        @endforeach
                                    </div>
                                @else
                                    <div>Нет товаров</div>
                                @endif
                            </div>
                            <!--._load-->
                            @if(count($products))
                                <div class="cat-view__load">
                                   @include('paginations.load_more', ['paginator' => $products])
                                </div>
                                <div class="cat-view__pagination">
                                    @include('paginations.with_pages', ['paginator' => $products])
                                </div>
                            @endif
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
