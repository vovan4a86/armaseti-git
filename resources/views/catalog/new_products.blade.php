@extends('template')
@section('content')
    @include('blocks.bread')
    <!--.layout-->
    <div class="layout">
        <div class="layout__container container">
            <!--aside._aside-->
            @include('catalog.aside_filter_new_prod')
            <!--main._main-->
            <main class="layout__main">
                <div class="page">
                    <!--mobile filter-->
{{--                    @include('catalog.mobile_filter')--}}
                    <!--._item-->
                    @if(count($main_products_categories))
                        <div class="page__item">
                            <div class="b-nav">
                                <nav class="b-nav__nav">
                                    <ul class="b-nav__list list-reset">
                                        @foreach($main_products_categories as $category)
                                            <li class="b-nav__item">
                                                <a class="b-nav__link {{ $category->isActive ? 'is-active' : '' }}"
                                                   href="{{ $category->url }}" data-link="data-link">
                                                    {{ $category->name }}
                                                </a>
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
                </div>
            </main>
        </div>
    </div>

    <!--section.s-req-->
    @include('blocks.request')
@endsection
