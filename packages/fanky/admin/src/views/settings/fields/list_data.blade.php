<table class="table">
	<tbody class="setting-items-list">
		@foreach ($setting->value as $n => $item)
			<tr>
				<td width="40" align="center" style="vertical-align:middle;">
					<span class="glyphicon glyphicon-sort handle" style="cursor:pointer;"></span>
				</td>
				<td>
					<dl class="dl-horizontal s-dl">
						@foreach (array_get($setting->params, 'fields', []) as $field => $params)
							<dt>{{ array_get($params, 'title') }}</dt>
							<dd>
								@if ($params['type'] == 0)
									@include('admin::settings.fields.input', ['name' => "setting[".$setting->id."][$field][]", 'value' => array_get($item, $field, ''), 'placeholder' => array_get($params, 'title')])
								@elseif ($params['type'] == 1)
									@include('admin::settings.fields.textarea', ['name' => "setting[".$setting->id."][$field][]", 'value' => array_get($item, $field, ''), 'placeholder' => array_get($params, 'title')])
								@elseif ($params['type'] == 2)
									@include('admin::settings.fields.editor', ['id' => 'setting_'.$setting->id.'_'.$field.'_'.$n, 'name' => "setting[".$setting->id."][$field][]", 'value' => array_get($item, $field)])
								@elseif ($params['type'] == 3)
									@include('admin::settings.fields.file', ['setting' => $setting, 'name' => "setting[".$setting->id."][$field][]", 'value' => array_get($item, $field, ''), 'placeholder' => array_get($params, 'title')])
								@endif
							</dd>
						@endforeach
					</dl>
				</td>
				<td width="40" align="center" style="vertical-align:middle;">
					<a class="glyphicon glyphicon-trash" href="#" style="color:red;" title="Удалить" onclick="return settingsListItemDel(this)"></a>
				</td>
			</tr>
		@endforeach
	</tbody>
	<tfoot>
		<tr style="display:none;">
			<td width="40" align="center" style="vertical-align:middle;">
				<span class="glyphicon glyphicon-sort handle" style="cursor:pointer;"></span>
			</td>
			<td>
				<dl class="dl-horizontal">
					@foreach (array_get($setting->params, 'fields', []) as $field => $params)
						<dt>{{ array_get($params, 'title') }}</dt>
						<dd>
							@if ($params['type'] == 0)
								@include('admin::settings.fields.input', ['name' => "setting[".$setting->id."][$field][]", 'value' => '', 'placeholder' => array_get($params, 'title')])
							@elseif ($params['type'] == 1)
								@include('admin::settings.fields.textarea', ['name' => "setting[".$setting->id."][$field][]", 'value' => '', 'placeholder' => array_get($params, 'title')])
							@elseif ($params['type'] == 2)
								<textarea id="{{ 'setting_'.$setting->id.'_'.$field.'_' }}" class="s-editor" name="{{ 'setting['.$setting->id.']['.$field.'][]' }}" rows="10" cols="80"></textarea>
							@elseif ($params['type'] == 3)
								@include('admin::settings.fields.file', ['setting' => $setting, 'name' => "setting[".$setting->id."][$field][]", 'value' => '', 'placeholder' => array_get($params, 'title')])
							@endif
						</dd>
					@endforeach
				</dl>
			</td>
			<td width="40" align="center" style="vertical-align:middle;">
				<a class="glyphicon glyphicon-trash" href="#" style="color:red;" title="Удалить" onclick="return settingsListItemDel(this)"></a>
			</td>
		</tr>
		<tr>
			<td colspan="3"><a class="btn-link" href="#" onclick="return settingsListItemAdd(this)">Добавить</a></td>
		</tr>
	</tfoot>
</table>