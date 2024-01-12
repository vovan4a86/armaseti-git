<span class="images_item">
	<img class="img-polaroid" src="{{ $image->imageSrc() }}"
		 style="cursor:pointer;" data-image="{{ $image->thumb(2) }}"
		 onclick="popupImage('{{ $image->thumb(2) }}')">
	<a class="images_del" href="{{ route('admin.news.newsImageDel', [$image->id]) }}"
	   onclick="return newsImageDel(this)">
		<span class="glyphicon glyphicon-trash"></span>
	</a>
</span>
