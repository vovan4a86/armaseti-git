@if($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator
    && $paginator->hasPages()
    && $paginator->lastPage() > 1)
    <? /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */ ?>

    <?php
    // config
    $link_limit = 7; // maximum number of links (a little bit inaccurate, but will be ok for now)
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
        <div class="pagination">
            <ul class="pagination__list list-reset">
                @if ($paginator->currentPage() > 1)
                    <li class="pagination__item">
                        <a class="pagination__link {{ $paginator->previousPageUrl() ? '' : 'is-disabled' }}" href="{{ $paginator->previousPageUrl() }}">
                            <span class="pagination__icon iconify" data-icon="ph:caret-left"></span>
                        </a>
                    </li>
                @endif
                @if($from > 1)
                    <li class="pagination__item">
                        <a class="pagination__link" href="{{ $paginator->url(1) }}">1</a>
                    </li>
                @endif

                @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                    @if ($from < $i && $i < $to)
                        <li class="pagination__item">
                            <a class="pagination__link {{ $i == $paginator->currentPage() ? 'is-disabled' : '' }}" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endif
                @endfor

                @if($to < $paginator->lastPage())
                    <li class="pagination__item">
                        <a class="pagination__link" href="{{ $paginator->url($paginator->lastPage()) }}">...</a>
                    </li>
                @endif

                @if ($paginator->currentPage() < $paginator->lastPage())
                    <li class="pagination__item">
                        <a class="pagination__link {{ $paginator->nextPageUrl() ? '' : 'is-disabled' }}" href="{{ $paginator->nextPageUrl() }}">
                            <span class="pagination__icon iconify" data-icon="ph:caret-right"></span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    @endif
@endif
