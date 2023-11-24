<li data-id="{{ $gallery->id }}">
	<span class="handle ui-sortable-handle" style="vertical-align:middle;">
		<i class="fa fa-ellipsis-v"></i>
		<i class="fa fa-ellipsis-v"></i>
	</span>
	<span class="text" style="vertical-align:middle;">
		<a href="{{ route('admin.gallery.items', [$gallery->id]) }}" onclick="return galleryOpen(this)">{{ $gallery->name }}</a>
		<form action="{{ route('admin.gallery.gallerySave') }}" onsubmit="return gallerySave(this)" style="display:none;">
			<input type="hidden" name="id" value="{{ $gallery->id }}">
			<div class="input-group input-group-sm">
				<input type="text" class="form-control" name="name" value="{{ $gallery->name }}" placeholder="Название галереи...">
				<span class="input-group-btn">
					<button class="btn btn-success btn-flat" type="submit"><span class="glyphicon glyphicon-ok"></span></button>
				</span>
			</div>
		</form>
	</span>
	<div class="tools">
		<i class="fa fa-edit" onclick="return galleryEdit(this)"></i>
		<i class="fa fa-trash-o" data-url="{{ route('admin.gallery.galleryDel', [$gallery->id]) }}" onclick="return galleryDel(this)"></i>
	</div>
</li>