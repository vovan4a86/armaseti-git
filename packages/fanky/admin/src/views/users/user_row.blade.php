<tr data-id="{{ $item->id }}">
	<td>
		<a href="{{ route('admin.users.edit', [$item->id]) }}" onclick="popupAjax($(this).attr('href')); return false;">{{ $item->username }}</a>
	</td>
	<td>
		{{ $item->name }}
	</td>
	<td>
		{{ $item->roleName }}
	</td>
	<td>
		<a class="glyphicon glyphicon-trash" href="{{ route('admin.users.del', [$item->id]) }}" style="font-size:20px; color:red;" onclick="return userDel(this)"></a>
	</td>
</tr>