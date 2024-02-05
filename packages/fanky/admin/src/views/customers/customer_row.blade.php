<tr data-id="{{ $item->id }}">
	<td>
		<a href="{{ route('admin.customers.edit', [$item->id]) }}"
		   onclick="popupAjax($(this).attr('href')); return false;">
			{{ $item->email }}
		</a>
	</td>
	<td>
		{{ $item->name }}
	</td>
	<td>
		{{ $item->phone }}
	</td>
	<td>
		<a class="glyphicon glyphicon-trash"
		   href="{{ route('admin.customers.delete', [$item->id]) }}"
		   style="font-size:20px; color:red;"
		   onclick="return customerDel(this)"></a>
	</td>
</tr>
