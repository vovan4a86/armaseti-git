<tr id="doc{{ $doc->id }}">
    <td width="5%" style="text-align: center;"><img src="/static/images/common/ico_doc.svg"></td>
    <td width="10%">{{ $doc->getFileSizeAttribute() }}</td>
    <td width="10%"><a href="{{ \Fanky\Admin\Models\ProductDoc::UPLOAD_URL . $doc->file }}" target="_blanc">Открыть</a></td>
    <td width="70%">{{ $doc->name }}</td>
    <td width="5%">
        <a href="{{ route('admin.catalog.del_doc', [$doc->id]) }}"
           class="btn btn-default del-param" onclick="delDoc(this, event)">
            <i class="fa fa-trash text-red"></i></a>
    </td>
</tr>
