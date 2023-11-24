@if ($setting->type == 4 || $setting->type == 6)
	<div class="form-group">
		<label>Поля</label>
		<table class="table">
			<tbody class="settings-params-list">
				@foreach (array_get($setting->params, 'fields', []) as $data_key => $data_field)
					<tr>
						<td width="40" align="center" style="vertical-align:middle;">
							<span class="handle glyphicon glyphicon-sort" style="cursor:pointer;"></span>
						</td>
						<td>
							<div style="margin-bottom:3px;"><input type="text" class="form-control" name="params[fields][title][]" value="{{ $data_field['title'] }}" placeholder="Название"/></div>
							<div class="row">
								<div class="col-xs-6"><input type="text" class="form-control" name="params[fields][key][]" value="{{ $data_key }}" placeholder="Ключ"/></div>
								<div class="col-xs-6">
									<select class="form-control" name="params[fields][type][]">
										@foreach (array_only($setting::$types, [0,1,2,3]) as $typeId => $typeName)
											<option value="{{ $typeId }}" {{ $data_field['type'] == $typeId ? 'selected' : '' }}>{{ $typeName }}</option>
										@endforeach
									</select>
								</div>
							</div>
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
						<span class="handle glyphicon glyphicon-sort" style="cursor:pointer;"></span>
					</td>
					<td>
						<div style="margin-bottom:3px;"><input type="text" class="form-control" name="params[fields][title][]" value="" placeholder="Название"/></div>
						<div class="row">
							<div class="col-xs-6"><input type="text" class="form-control" name="params[fields][key][]" value="" placeholder="Ключ"/></div>
							<div class="col-xs-6">
								<select class="form-control" name="params[fields][type][]">
									@foreach (array_only($setting::$types, [0,1,2,3]) as $typeId => $typeName)
										<option value="{{ $typeId }}">{{ $typeName }}</option>
									@endforeach
								</select>
							</div>
						</div>
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
	</div>
	<script type="text/javascript"> $('.settings-params-list').sortable({handle: '.handle'}); </script>
@elseif ($setting->type == 7)

@endif