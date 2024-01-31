<span class="images_item" data-id="{{ $image->id }}">
	<img class="img-polaroid" src="{{ $image->imageSrc($alias) }}"
		 style="cursor:pointer;" data-image="{{ $image->thumb(2, $alias) }}"
		 onclick="popupImage('{{ $image->thumb(2, $alias) }}')">
	<a class="images_del" href="{{ route('admin.catalog.catalogGalleryImageDelete', [$image->id]) }}"
	   onclick="return catalogGalleryImageDelete(this)">
		<span class="glyphicon glyphicon-trash"></span>
	</a>
</span>
