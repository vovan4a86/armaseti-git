<li class="{{ $active->id == $group->id ? 'active' : '' }}" data-id="{{ $group->id }}">
	<div class="tree-item">
		<span class="tree-handle ui-sortable-handle" style="vertical-align:middle;">
			<i class="fa fa-ellipsis-v"></i>
			<i class="fa fa-ellipsis-v"></i>
		</span>
		<span class="text tree-item-name" style="vertical-align:middle;">
			<a class="" href="{{ route('admin.settings.groupItems', [$group->id]) }}">{{ $group->name }}</a>
			<form action="{{ route('admin.settings.groupSave') }}" onsubmit="return settingsGroupSave(this)" style="display:none;">
				<input type="hidden" name="id" value="{{ $group->id }}">
				<div class="input-group input-group-sm">
					<input type="text" class="form-control" name="name" value="{{ $group->name }}" placeholder="Название галереи...">
					<span class="input-group-btn">
						<button class="btn btn-success btn-flat" type="submit"><span class="glyphicon glyphicon-ok"></span></button>
					</span>
				</div>
			</form>
		</span>
		<div class="tree-tools">
			<a href="#" onclick="return settingsGroupEdit(this)"><i class="fa fa-edit"></i></a>
			<a href="#" data-url="{{ route('admin.settings.groupDel', [$group->id]) }}" onclick="return settingsGroupDel(this)"><i class="fa fa-trash-o"></i></a>
		</div>
	</div>
</li>