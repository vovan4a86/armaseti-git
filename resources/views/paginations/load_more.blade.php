@if($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator
    && $paginator->hasPages()
    && $paginator->lastPage() > 1)

    @if($paginator->hasMorePages())
        <div class="news-layout__row news-layout__row--loader">
            <button class="b-loader btn-reset" type="button" aria-label="Загрузить еще"
                    data-url="{{ $paginator->nextPageUrl() }}">
                <span>Загрузить еще</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="18" fill="none">
                    <path fill="currentColor" stroke="currentColor" stroke-width=".7"
                          d="M8.28 17a7.281 7.281 0 0 0 2.694-14.045l1.943-1.23a.393.393 0 0 0-.421-.664L9.867 2.723l-.012.008-.004.003c-.002 0-.003.002-.005.003a.395.395 0 0 0-.022.016l-.01.007a.388.388 0 0 0-.023.02l-.01.01a.417.417 0 0 0-.014.016l-.007.007a.385.385 0 0 0-.02.026l-.008.013a.375.375 0 0 0-.008.013l-.007.011a.388.388 0 0 0-.014.028l-.007.018-.002.006-.008.02c-.001.006-.004.01-.006.016 0 .005 0 .009-.002.013l-.002.017L9.675 3c-.002.009-.004.017-.004.026l-.003.028v.012l.001.013.001.024c0 .01.001.018.003.027l.003.015.003.012.003.016.787 2.811a.394.394 0 0 0 .758-.212l-.588-2.102a6.493 6.493 0 1 1-4.036-.223.393.393 0 0 0-.203-.76A7.28 7.28 0 0 0 8.28 17Z"
                    />
                </svg>
            </button>
        </div>
    @endif
@endif
<script src="/static/js/btns.js"></script>
