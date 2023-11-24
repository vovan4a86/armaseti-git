<div class="box box-solid">
	<div class="box-header">
		<h3 class="box-title">{{ $gallery->name }}</h3>
	</div>

	<div class="box-body">
		@include('admin::gallery.items', ['gallery' => $gallery, 'items' => $items])
	</div>
</div>