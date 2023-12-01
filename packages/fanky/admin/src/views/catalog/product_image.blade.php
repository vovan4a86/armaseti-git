<span class="images_item">
	<img class="img-polaroid" src="{{ $image->imageSrc($alias) }}"
		 style="cursor:pointer;" data-image="{{ $image->thumb(2, $alias) }}"
		 onclick="popupImage('{{ $image->thumb(2, $alias) }}')">
	<a class="images_del" href="{{ route('admin.catalog.productImageDel', [$image->id]) }}"
	   onclick="return productImageDel(this)">
		<span class="glyphicon glyphicon-trash"></span>
	</a>
</span>
