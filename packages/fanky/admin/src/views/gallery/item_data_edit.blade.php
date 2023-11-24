<form action="{{ route('admin.gallery.imageDataSave', [$image->id]) }}" onsubmit="galleryImageDataSave(this, event)" style="width:600px;">

	@foreach ($image->gallery->params['fields'] as $field => $params)
		<div class="form-group">
			<label for="gallery-image-{{ $field }}">{{ $params['title'] }}</label>
			@if ($params['type'] == 0)
				<input id="gallery-image-{{ $field }}" class="form-control" type="text" name="{{ $field }}" value="{{ $image->data[$field] ?? '' }}">
			@elseif ($params['type'] == 1)
				<textarea id="gallery-image-{{ $field }}" class="form-control" name="{{ $field }}" rows="4">{{ $image->data[$field] ?? '' }}</textarea>
			@endif
		</div>
	@endforeach

	<button class="btn btn-primary" type="submit">Сохранить</button>
</form>