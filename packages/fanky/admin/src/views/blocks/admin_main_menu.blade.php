<ul class="sidebar-menu" data-widget="tree">
    @foreach(Menu::get('main_menu')->roots() as $item)
		<?php /** @var \Lavary\Menu\Item $item */ ?>
        <li class="{{ $item->hasChildren()? 'treeview': '' }} {{ $item->isActive? ' active': '' }}">
            <a href="{!! $item->hasChildren() ? '#': $item->url() !!}"
                    {!! ($link_class = array_get($item->attr(), 'link_class'))? 'class="'.$link_class.'"': '' !!}>
                @if(array_get($item->attr(), 'icon'))
                    <i class="fa fa-fw {{ array_get($item->attr(), 'icon') }}"></i>
                @endif
                <span>{!! $item->title !!}</span>
                @if($item->hasChildren())
                    <span class="pull-right-container">
                                  <i class="fa fa-angle-left pull-right"></i>
                                </span>
                @endif
            </a>
            @if($item->hasChildren())
                <ul class="treeview-menu">
                    @foreach($item->children() as $child)
                        <li class="{{ $child->isActive? ' active': '' }}">
                            <a href="{{ $child->url() }}" {!! ($link_class = array_get($child->attr(), 'link_class'))? 'class="'.$link_class.'"': '' !!}>
                                @if(array_get($child->attr(), 'icon'))
                                    <i class="fa fa-fw {{ array_get($child->attr(), 'icon') }}"></i>
                                @endif
                                <span>{!! $child->title !!}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </li>
    @endforeach
</ul>