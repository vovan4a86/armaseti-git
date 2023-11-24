@extends('admin::template')

@section('scripts')
	<script type="text/javascript" src="/adminlte/plugins/ckeditor/ckeditor.js"></script>
	<script src="https://code.jquery.com/ui/1.11.2/jquery-ui.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="/adminlte/interface_settings.js"></script>
@stop

@section('page_name')
	<h1>
		Настройки
	</h1>
@stop

@section('breadcrumb')
	<ol class="breadcrumb">
		<li><a href="{{ route('admin') }}"><i class="fa fa-dashboard"></i> Главная</a></li>
		<li class="active">Настройки</li>
	</ol>
@stop

@section('content')
	<div class="row">
		<div class="col-md-3">
			<div class="box box-solid">
				<div class="box-header"><h3 class="box-title">Группы</h3></div>

				<div class="box-body">
					<ul id="setting-groups" class="tree-lvl ui-sortable">
						@foreach ($groups as $item)
							@include('admin::settings.group_row', ['group' => $item, 'active' => $group])
						@endforeach
					</ul>
				</div>

				<div class="box-footer">
					<form action="{{ route('admin.settings.groupSave') }}" onsubmit="return settingsGroupCreate(this)">
						<div class="input-group input-group-sm">
							<input type="text" class="form-control" name="name" value="" placeholder="Название группы...">
							<span class="input-group-btn">
								<button class="btn btn-success btn-flat" type="submit">Создать</button>
							</span>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div id="settings-content" class="col-md-9">
			@if ($group->id)
				@include('admin::settings.group_items', ['group' => $group, 'settings' => $settings])
			@endif
		</div>
	</div>
@stop