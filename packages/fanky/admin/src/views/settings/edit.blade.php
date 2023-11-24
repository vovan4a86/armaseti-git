<form action="{{ route('admin.settings.editSave') }}" onsubmit="settingsSaveEdit(this, event)" style="width:600px;">
	<input type="hidden" name="id" value="{{ $setting->id }}">
	<input type="hidden" name="group_id" value="{{ $setting->group_id }}">

	<div class="form-group">
		<label for="setting-name">Название</label>
		<input id="setting-name" class="form-control" type="text" name="name" value="{{ $setting->name }}">
	</div>

	<div class="form-group">
		<label for="setting-description">Описание (подсказка)</label>
		<textarea id="setting-description" class="form-control" name="description" rows="2">{{ $setting->description }}</textarea>
	</div>

	<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				<label for="setting-code">Системный ключ</label>
				<input id="setting-code" class="form-control" type="text" name="code" value="{{ $setting->code }}">
			</div>
		</div>

		<div class="col-md-6">
			<div class="form-group">
				<label for="setting-type">Тип</label>
				<select id="setting-type" class="form-control" name="type" data-url="{{ route('admin.settings.blockParams') }}" onchange="settingsBlocParams(this, event)">
					@foreach ($setting::$types as $typeId => $typeName)
						<option value="{{ $typeId }}" {{ $setting->type == $typeId ? 'selected' : '' }}>{{ $typeName }}</option>
					@endforeach
				</select>
			</div>
		</div>
	</div>

	<div id="setting-params">
		@include('admin::settings.edit_params', ['setting' => $setting])
	</div>

	<hr>

	<button class="btn btn-primary" type="submit">Сохранить</button>

</form>