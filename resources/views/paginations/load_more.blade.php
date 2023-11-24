@if($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator
    && $paginator->hasPages()
    && $paginator->lastPage() > 1)
        <? /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */ ?>

        <?php
        // config
        $link_limit = 7; // maximum number of links (a bit inaccurate, but will be ok for now)
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
        @if($paginator->hasMorePages())
            <div class="s-objects__action">
                <a class="btn btn--primary btn-reset" href="{{ $paginator->nextPageUrl() }}"
                   onclick="moreNews(this, event)"
                   type="button" aria-label="Показать ещё">
                    <span>Показать ещё</span>
                </a>
            </div>
        @endif
    @endif
@endif