<span class="docs_item" data-id="{{ $doc->id }}">
	<div style="text-align: center; font-size: 12px; color: red">{{ $doc->name }}</div>
	<a href="{{ $doc->fileSrc($catalog->slug) }}" target="_blank">
		<img class="img-polaroid" src="{{ \Fanky\Admin\Models\ProductDoc::DOC_ICON }}" width="100"
			 style="cursor:pointer;" title="Открыть в новом окне">
	</a>
	<a class="docs_del" href="{{ route('admin.catalog.catalog-del-doc', [$doc->id]) }}"
       onclick="return catalogDocDel(this)">
		<span class="glyphicon glyphicon-trash"></span>
	</a>
	<a class="docs_edit" href="{{ route('admin.catalog.catalog-edit-doc', [$doc->id]) }}"
       onclick="catalogDocEdit(this, event)"><span class="glyphicon glyphicon-edit"></span></a>
</span>
