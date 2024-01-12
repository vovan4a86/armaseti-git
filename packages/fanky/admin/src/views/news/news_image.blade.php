<span class="images_item">
	<img class="img-polaroid" src="{{ $image->thumb(2) }}"
		 style="cursor:pointer;" data-image="{{ $image->thumb(2) }}"
		 onclick="popupImage('{{ $image->imageSrc() }}')">
	<a class="images_del" href="{{ route('admin.news.newsImageDel', [$image->id]) }}"
	   onclick="return newsImageGalleryDel(this)">
		<span class="glyphicon glyphicon-trash"></span>
	</a>
</span>
