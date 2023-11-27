@extends('template_test')
@section('content')
    <main>
        <div class="container">
            @include('blocks.bread')
            <div class="row">
                <div class="col-sm-3">
                    <h4>Фильтр:</h4>
                    <form class="filters" id="filter-form" action="{{ $category->url }}">
                        @if($filters_list)
                            @foreach($filters_list as $name => $items)
                                <div class="filter">
                                    <div class="filter-name">{{ $name }}</div>
                                    @foreach($items['values'] as $val)
                                        <label>
                                            <input type="checkbox" name="{{ $items['translit'] }}[]" value="{{ $val }}">
                                            <span class="filter-label-name">{{ $val }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
                        <div class="btns">
                            <button type="submit" class="btn btn-success">Применить</button>
                        </div>
                    </form>
                </div>
                <div class="col-sm-9">
                    <div class="categories">
                        @if(count($children))
                            <ul class="categories-list">
                                @foreach($children as $child)
                                    <li>
                                        <a href="{{ $child->url }}">{{ $child->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <h1>{{ $category->h1 }}</h1>
                    @if(count($products))
                        <ul class="products">
                            @foreach($products as $product)
                                @include('catalog.product_item')
                            @endforeach
                        </ul>
                    @else
                        <p>Пусто</p>
                    @endif
                </div>
            </div>
        </div>
    </main>
@endsection
