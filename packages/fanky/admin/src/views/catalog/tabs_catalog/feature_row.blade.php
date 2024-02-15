<tr data-id="{{ $item->id }}">
    <td width="40"><i class="fa fa-ellipsis-v"></i> <i class="fa fa-ellipsis-v"></i></td>
    <td width="100"><img src="{{ $item->imageSrc() }}" alt="thumb"></td>
    <td>{{ $item->text }}</td>
    <td width="60">
        <a class="glyphicon glyphicon-edit" href="{{ route('admin.catalog.feature-edit', [$item->id]) }}"
           style="font-size:20px; color:orange;" onclick="return featureEdit(this, event)"></a>
    </td>
    <td width="60">
        <a class="glyphicon glyphicon-trash" href="{{ route('admin.catalog.feature-delete', [$item->id]) }}"
           style="font-size:20px; color:red;" onclick="featureDelete(this, event)"></a>
    </td>
</tr>
