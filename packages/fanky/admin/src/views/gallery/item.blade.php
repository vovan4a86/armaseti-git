<span class="images_item" data-id="{{ $item->id }}">
	<img class="img-polaroid" src="{{ $item->thumb(1) }}" style="cursor:pointer;" data-image="{{ $item->src }}" onclick="return popupImage($(this).data('image'))">
	<a class="images_del" href="{{ route('admin.gallery.imageDel', [$item->id]) }}" onclick="return galleryItemDel(this)"><span class="glyphicon glyphicon-trash"></span></a>
	@if (!empty($gallery->params) && !empty($gallery->params['fields']))
		<a class="images_edit" href="{{ route('admin.gallery.imageEdit', [$item->id]) }}" onclick="galleryItemEdit(this, event)"><span class="glyphicon glyphicon-edit"></span></a>
	@endif
</span>