<ul class="tree-lvl">
	@foreach($items as $item)
		@if ($item->parent_id == $parent)
			<li data-id="{{ $item->id }}">
				@include('admin::pages.tree_item', ['item' => $item])
				@include('admin::pages.tree_lvl', ['parent' => $item->id, 'items' => $items])
			</li>
		@endif
	@endforeach
</ul>