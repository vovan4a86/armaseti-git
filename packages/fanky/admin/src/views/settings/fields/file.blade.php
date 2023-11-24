<div class="s-file-field">
	<label class="btn btn-success btn-xs">Загрузить файл
		<input type="file" name="{{ $name ?? 'setting['.$setting->id.']' }}" onchange="settingAttacheFile(this, event)" style="display:none;">
	</label>
	<input class="s-file-field-value" type="hidden" name="{{ $name ?? 'setting['.$setting->id.']' }}" value="{{ $value ?? $setting->value }}">
	<div class="s-file-item">
		@if (isset($value) && $value || (!isset($value) && $setting->value))
			@if (array_search(strtolower(pathinfo(base_path().$setting::UPLOAD_PATH.(isset($value) ? $value : $setting->value), PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'ico', 'svg']) !== false)
				<span class="images_item">
					<img class="img-polaroid" src="{{ $setting::UPLOAD_URL.(isset($value) ? $value : $setting->value) }}" style="cursor:pointer;" onclick="popupImage($(this).attr('src'))">
					<a class="images_del" href="#" onclick="settingsFileDel(this, event)"><span class="glyphicon glyphicon-trash"></span></a>
				</span>
			@else
				<div class="margin">
					<a class="text-light-blue" target="_blank" href="{{ url($setting::UPLOAD_URL.(isset($value) ? $value : $setting->value)) }}">
						<i class="fa fa-fw fa-download"></i>
						загруженный файл
					</a>
					<a class="text-red" href="#" onclick="settingsFileDel(this, event)"><i class="fa fa-fw fa-trash-o"></i> удалить файл</a>
				</div>
			@endif
		@endif
	</div>
</div>