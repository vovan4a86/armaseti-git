<span class="docs_item" data-id="{{ $doc->id }}">
	<div style="text-align: center; font-size: 12px; color: red">{{ $doc->name }}</div>
	<a href="{{ $doc->file_src }}" target="_blank">
		<img class="img-polaroid" src="{{ \Fanky\Admin\Models\ProductDoc::DOC_ICON }}" width="100"
			 style="cursor:pointer;" title="Открыть в новом окне">
	</a>
	<a class="docs_del" href="{{ route('admin.catalog.product-del-doc', [$doc->id]) }}"
       onclick="return productDocDel(this)">
		<span class="glyphicon glyphicon-trash"></span>
	</a>
	<a class="docs_edit" href="{{ route('admin.catalog.product-edit-doc', [$doc->id]) }}"
       onclick="productDocEdit(this, event)"><span class="glyphicon glyphicon-edit"></span></a>
</span>
