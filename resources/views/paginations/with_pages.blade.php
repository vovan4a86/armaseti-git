@if($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator
    && $paginator->hasPages()
    && $paginator->lastPage() > 1)
    <? /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */ ?>

    <?php
    // config
    $link_limit = 10; // maximum number of links (a little bit inaccurate, but will be ok for now)
    $half_total_links = floor($link_limit / 2);
    $from = $paginator->currentPage() - $half_total_links;
    $to = $paginator->currentPage() + $half_total_links;
    if ($paginator->currentPage() < $half_total_links) {
        $to += $half_total_links - $paginator->currentPage();
    }
    if ($paginator->lastPage() - $paginator->currentPage() < $half_total_links) {
        $from -= $half_total_links - ($paginator->lastPage() - $paginator->currentPage()) - 1;
    }
    ?>

    @if ($paginator->lastPage() > 1)
        <div class="b-pagination">
            {{--                @if ($paginator->currentPage() > 1)--}}
            <a class="b-pagination__link b-pagination__link--btn {{ $paginator->previousPageUrl() ? '' : 'is-disabled' }}"
               href="{{ $paginator->previousPageUrl() }}" title="Назад">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="1.6" d="M11.459 6.5H1.542m0 0L6.5 11.458M1.542 6.5 6.5 1.542"/>
                </svg>
            </a>
            {{--                @endif--}}

            <div class="b-pagination__pages">
                @if($from > 1)
                    <a class="b-pagination__link is-active" href="{{ $paginator->url(1) }}" title="Страница 1">1</a>
                @endif

                @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                    @if ($from < $i && $i < $to)
                        <a class="b-pagination__link {{ $i == $paginator->currentPage() ? 'is-active' : '' }}"
                           href="{{ $paginator->url($i) }}" title="Страница {{ $i }}">{{ $i }}</a>
                    @endif
                @endfor

                @if($to < $paginator->lastPage())
                    <a class="b-pagination__link" href="{{ $paginator->url($paginator->lastPage()) }}"
                       title="Последняя страница">...</a>
                @endif
            </div>

            {{--                @if ($paginator->currentPage() < $paginator->lastPage())--}}
            <a class="b-pagination__link b-pagination__link--btn {{ $paginator->nextPageUrl() ? '' : 'is-disabled' }}"
               href="{{ $paginator->nextPageUrl() }}" title="Дальше">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="1.6" d="M1.542 6.5h9.917m0 0-4.958 4.958M11.459 6.5 6.501 1.542"/>
                </svg>
            </a>
            {{--                @endif--}}
        </div>
    @endif
@endif
