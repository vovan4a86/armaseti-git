<div>
	<label class="btn btn-success btn-xs">Загрузить изображения
		<input type="file" name="setting[{{ $setting->id }}][]" multiple style="display:none;" onchange="settingAttacheGalleryImage(this, event)">
	</label>
</div>
<div class="setting-gal-list" style="position:relative;">
	@foreach ($setting->value as $item)
		<span class="images_item">
			<input type="hidden" name="setting[{{ $setting->id }}][]" value="{{ $item }}">
			<span class="images_move"><i class="fa fa-arrows"></i></span>
			<img class="img-polaroid" src="{{ $setting::UPLOAD_URL.$item }}" style="cursor:pointer;" onclick="popupImage($(this).attr('src'))">
			<a class="images_del" href="#" onclick="settingGalleryImageDel(this, event)"><span class="glyphicon glyphicon-trash"></span></a>
		</span>
	@endforeach
</div>