@foreach ($settings as $setting)
	<div class="setting-item" data-id="{{ $setting->id }}">
		<div class="form-group">
			<div>
				<label>{{ $setting->name }}</label>
				<a class="popup-ajax pull-right" href="{{ route('admin.settings.edit', [$setting->id]) }}">редактировать</a>
			</div>

			@if ($setting->type == 0)
				@include('admin::settings.fields.input', ['setting' => $setting])
			@elseif ($setting->type == 1)
				@include('admin::settings.fields.textarea', ['setting' => $setting])
			@elseif ($setting->type == 2)
				@include('admin::settings.fields.editor', ['setting' => $setting])
			@elseif ($setting->type == 3)
				@include('admin::settings.fields.file', ['setting' => $setting])
			@elseif ($setting->type == 4)
				@include('admin::settings.fields.data', ['setting' => $setting])
			@elseif ($setting->type == 5)
				@include('admin::settings.fields.list', ['setting' => $setting])
			@elseif ($setting->type == 6)
				@include('admin::settings.fields.list_data', ['setting' => $setting])
			@elseif ($setting->type == 7)
				@include('admin::settings.fields.gallery', ['setting' => $setting])
			@endif

			@if ($setting->description)
				<p class="help-block">{{ $setting->description }}</p>
			@endif
		</div>
	</div>
@endforeach