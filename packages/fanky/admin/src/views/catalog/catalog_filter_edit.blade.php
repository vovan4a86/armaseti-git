<form action="{{ route('admin.catalog.catalog-filter-save-data', [$catalog_id]) }}"
	  onsubmit="catalogFilterSaveData(this, event)" style="width:600px;">

		<div class="form-group">
			<input type="hidden" name="id" value="{{ $item->id }}">
			<label for="filter-name">Название</label>
			<input id="filter-name" class="form-control" type="text"
				   name="name" value="{{ $item->name }}">
			<div style="margin-top: 10px; color: red; font-size: 12px">
				Меняется название фильтра и у вложенных разделов, так же измениться и название характеристики у всех товаров раздела
			</div>
		</div>

	<button class="btn btn-primary" type="submit">Сохранить</button>
</form>
