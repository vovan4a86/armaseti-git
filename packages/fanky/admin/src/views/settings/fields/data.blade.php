<div class="form-group">
	<dl class="dl-horizontal s-dl">
		@foreach (array_get($setting->params, 'fields', []) as $field => $params)
			<dt>{{ array_get($params, 'title') }}</dt>
			<dd>
				@if ($params['type'] == 0)
					@include('admin::settings.fields.input', ['name' => "setting[".$setting->id."][$field]", 'value' => array_get($setting->value, $field, ''), 'placeholder' => array_get($params, 'title')])
				@elseif ($params['type'] == 1)
					@include('admin::settings.fields.textarea', ['name' => "setting[".$setting->id."][$field]", 'value' => array_get($setting->value, $field, ''), 'placeholder' => array_get($params, 'title')])
				@elseif ($params['type'] == 2)
					@include('admin::settings.fields.editor', ['id' => 'setting_'.$setting->id.'_'.$field, 'name' => "setting[".$setting->id."][$field]", 'value' => array_get($setting->value, $field, '')])
				@elseif ($params['type'] == 3)
					@include('admin::settings.fields.file', ['setting' => $setting, 'name' => "setting[".$setting->id."][$field]", 'value' => array_get($setting->value, $field, ''), 'placeholder' => array_get($params, 'title')])
				@endif
			</dd>
		@endforeach
	</dl>
</div>