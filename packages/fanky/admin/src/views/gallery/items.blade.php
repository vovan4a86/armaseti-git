<div class="gallery-block">
	<label class="btn btn-success">Загрузить изображения
		<input type="file" name="images[]" value="" multiple style="display:none;" onchange="return galleryUpload(this, event)" data-url="{{ route('admin.gallery.imageUpload', [$gallery->id]) }}">
	</label>
	<hr>
	<div class="gallery-items">
		@foreach ($items as $item)
			@include('admin::gallery.item', ['item' => $item, 'gallery' => $gallery])
		@endforeach
	</div>
</div>
<script type="text/javascript">
    $(".gallery-items").sortable({
        update: function(event, ui) {
            var url = "{{ route('admin.gallery.order') }}";
            var data = {};
            data.sorted = ui.item.closest('.gallery-items').sortable( "toArray", {attribute: 'data-id'} );
            sendAjax(url, data);
            //console.log(data);
        },
    }).disableSelection();
</script>