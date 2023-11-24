@if(isset($bread) && count($bread))
    <nav class="breadcrumbs">
        <ul class="breadcrumbs__list list-reset" itemscope="itemscope" itemtype="https://schema.org/BreadcrumbList">
            <li class="breadcrumbs__item" itemprop="itemListElement" itemscope="itemscope" itemtype="https://schema.org/ListItem">
                <a class="breadcrumbs__link" href="{{ route('main') }}" itemprop="item">
                    <span itemprop="name">Главная</span>
                    <meta itemprop="position" content="1" />
                </a>
            </li>
            @foreach($bread as $item)
                <li class="breadcrumbs__item" itemprop="itemListElement" itemscope
                    itemtype="https://schema.org/ListItem">
                    <a class="breadcrumbs__link" href="{!! $loop->last ? 'javascript:void(0)': $item['url'] !!}"
                       itemprop="item">
                        <span itemprop="name">{{ $item['name'] }}</span>
                        <meta itemprop="position" content="{{ $loop->index + 1 }}">
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
