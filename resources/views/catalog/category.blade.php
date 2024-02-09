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
                    @if(count($current_categories))
                        <div class="page__item">
                            <div class="b-nav">
                                <nav class="b-nav__nav">
                                    <ul class="b-nav__list list-reset">
                                        @foreach($current_categories as $category)
                                            <li class="b-nav__item">
                                                <a class="b-nav__link {{ $category->isActive ? 'is-active' : '' }}"
                                                   href="{{ $category->url }}"
                                                   data-link="data-link">{{ $category->name }}</a>
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
                                @if(count($products))
                                    <div class="cat-view__actions">
                                        @include('catalog.sorting')
                                        @include('catalog.toggles')
                                    </div>
                                    <!--._list(:class="listView && 'is-active'")-->
                                    <div class="cat-view__list" :class="listView &amp;&amp; 'is-active'">
                                        @foreach($products as $product)
                                            @include('catalog.product_item_catalog')
                                        @endforeach
                                    </div>
                                @else
                                    <p>Нет товаров</p>
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

                            @if(count($gallery))
                                <div class="text-block__gallery">
                                    @foreach($gallery as $item)
                                        <a class="text-block__gallery-item"
                                           href="{{ $item->imageSrc($item->catalog->alias) }}" data-fancybox="gallery"
                                           data-caption="caption">
                                            <img class="text-block__pic"
                                                 src="{{ $item->thumb(2, $item->catalog->alias) }}" width="116"
                                                 height="186" alt="name" loading="lazy">
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {!! $text_after !!}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!--section.s-req-->
    @include('blocks.request')
@endsection
