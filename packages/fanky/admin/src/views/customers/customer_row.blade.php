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
		@if ($item->details)
			<a href="{{ $item->file_src }}" target="_blank">Открыть файл</a>
		@endif
	</td>
	<td>
		<a class="glyphicon glyphicon-trash"
		   href="{{ route('admin.customers.delete', [$item->id]) }}"
		   style="font-size:20px; color:red;"
		   onclick="return customerDel(this)"></a>
	</td>
</tr>
