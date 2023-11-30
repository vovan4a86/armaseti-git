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
<style>
	.docs_del {
		top: 28px!important;
	}
	.docs_item{
		position: relative;
		display: inline-block;
	}
	.docs_item img{
		max-width: 400px;
		max-height: 113px;
	}
	.docs_item img.active{
		border: 1px solid green !important;
	}
	.docs_item .docs_del {
		display:none;
		position: absolute;
		top: 11px;
		right: 11px;
		padding: 3px 6px;
		background: rgba(255,0,0,.5);
		color: #fff;
	}
	.docs_item:hover .docs_del{
		display: block !important;
	}
	.docs_item .docs_edit {
		display:none;
		position: absolute;
		bottom: 11px;
		right: 11px;
		padding: 3px 6px;
		background: rgba(255,163,45,.7);
		color: #fff;
	}
	.docs_item:hover .docs_edit{
		display: block !important;
	}
</style>
