<table class="table">
	<tbody class="setting-items-list">
		@foreach ($setting->value as $item)
			<tr>
				<td width="40" align="center" style="vertical-align:middle;">
					<span class="glyphicon glyphicon-sort handle" style="cursor:pointer;"></span>
				</td>
				<td>
					<input type="text" class="form-control" name="setting[{{ $setting->id }}][]" value="{{ $item }}" />
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
				<input type="text" class="form-control" name="setting[{{ $setting->id }}][]" value="" />
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