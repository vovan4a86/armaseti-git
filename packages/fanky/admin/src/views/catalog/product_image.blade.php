<span class="images_item">
	<img class="img-polaroid" src="{{ $image->thumb(2) }}"
		 style="cursor:pointer;" data-image="{{ $image->thumb(2) }}"
		 onclick="popupImage('{{ $image->thumb(2) }}')">
	<a class="images_del" href="{{ route('admin.catalog.productImageDel', [$image->id]) }}"
	   onclick="return productImageDel(this)">
		<span class="glyphicon glyphicon-trash"></span>
	</a>
</span>
