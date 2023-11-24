<div class="tree-item{{ $item->published != 1 ? ' dont-show' : '' }}">
	<span class="tree-handle"><i class="fa fa-ellipsis-v"></i> <i class="fa fa-ellipsis-v"></i></span>
	<a class="tree-item-name" href="{{ route('admin.pages.edit', [$item->id]) }}" onclick="return pageContent(this)">{{ $item->name }}</a>
	<div class="tree-tools">
		<a href="{{ route('admin.pages.edit').'?parent='.$item->id }}" onclick="return pageContent(this)"><i class="fa fa-plus"></i></a>
		<a href="{{ route('admin.pages.del', [$item->id]) }}" onclick="return pageDel(this)"><i class="fa fa-trash-o"></i></a>
	</div>
</div>