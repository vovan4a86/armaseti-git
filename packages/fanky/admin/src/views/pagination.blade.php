<div class="clearfix" style="text-align:center;">
	<ul class="pagination pagination-sm">
		@if ($curent_page > 1)
			<li><a class="prev" href="{{ $url.Pagination::query(['p' => (($curent_page - 1) > 1 ? $curent_page - 1 : false)]) }}">◄</a></li>
		@else
			<li><a class="prev" href="#" onclick="return false">◄</a></li>
		@endif

		@for ($i = max(1, $curent_page-10); $i <= min($pages_count, $curent_page+10); $i++)
			@if ($i != $curent_page)
				<li><a href="{{ $url.Pagination::query(['p' => ($i > 1 ? $i : false)]) }}">{{ $i }}</a></li>
			@else
				<li><span>{{ $i }}</span></li>
			@endif
		@endfor

		@if ($curent_page < $pages_count)
			<li><a class="next" href="{{ $url.Pagination::query(['p' => $curent_page + 1]) }}">►</a></li>
		@else
			<li><a class="prev" href="#" onclick="return false">►</a></li>
		@endif
	</ul>
</div>