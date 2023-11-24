<tr id="param{{ $param->id }}">
    <td>{{ $param->name }}</td>
    <td>{{ $param->alias }}</td>
    <td>{{ $param->measure }}</td>
    <td>
        <a href="{{ route('admin.catalog.edit_param', [$param->id]) }}" class="btn btn-default edit-param" onclick="editParam(this, event)">
            <i class="fa fa-pencil text-yellow"></i></a>
        <a href="{{ route('admin.catalog.del_param', [$param->id]) }}" class="btn btn-default del-param" onclick="delParam(this, event)">
            <i class="fa fa-trash text-red"></i></a>
    </td>
</tr>
