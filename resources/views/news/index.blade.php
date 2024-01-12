@extends('template')
@section('content')
    @include('blocks.bread')
    <div class="news-layout">
        <div class="news-layout__container container">
            <div class="news-layout__grid">
                <!--main._main-->
                <main class="news-layout__main">
                    <section class="newses">
                        <div class="newses__title">Новости</div>
                        @if(count($items))
                            <div class="newses__grid">
                                @foreach($items as $item)
                                    @include('news.newses_item')
                                @endforeach
                            </div>
                        @endif
                    </section>
                </main>
                <!--aside._aside-->
                @include('news.aside')
            </div>
            @include('paginations.load_more', ['paginator' => $items])
            <div class="news-layout__row">
                @include('paginations.with_pages', ['paginator' => $items])
            </div>
        </div>
    </div>
@stop
