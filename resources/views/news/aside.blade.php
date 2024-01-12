<aside class="news-layout__aside">
    @if(count($aside_items))
        <div class="a-newses">
            @foreach($aside_items as $item)
                @include('news.a_newses_item')
            @endforeach
        </div>
    @endif
</aside>
