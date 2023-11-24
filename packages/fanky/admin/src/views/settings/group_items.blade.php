<form action="{{ route('admin.settings.save') }}" method="post" enctype="multipart/form-data" onsubmit="settingsSave(this, event)">
	<input type="hidden" name="_token" value="{{ csrf_token() }}">
	<input type="hidden" name="group_id" value="{{ $group->id }}">
	
	<div class="box box-solid">
		<div class="box-header">
			<h3 class="box-title">
				{{ $group->name }}
			</h3>
		</div>

		<div class="box-body">
			@if ($group->description)
				<p class="lead">{{ $group->description }}</p>
			@endif

			<a class="margin popup-ajax" href="{{ route('admin.settings.edit').'?group='.$group->id }}">Добавить настройку</a>
			<div id="settings-group-{{ $group->id }}">
				@include('admin::settings.items', ['settings' => $settings])
			</div>
		</div>

		<div class="box-footer">
			<button type="submit" class="btn btn-primary">Сохранить</button>
		</div>
	</div>
</form>

<script type="text/javascript"> $('.setting-items-list').sortable({handle: '.handle'}); </script>
<script type="text/javascript"> $('.setting-gal-list').sortable({handle: '.images_move'}); </script>