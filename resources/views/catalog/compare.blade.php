@extends('template_test')
@section('content')
    <main>
        <div class="container">
            @include('blocks.bread')
            <div class="row">
                <div class="col-sm-12">
                    <h1>Сравнение товаров: </h1>
                    <a href="{{ route('catalog.compare') }}">Все</a>
                    <a href="{{ route('catalog.compare', ['diff' => 1]) }}">Различия</a>
                    <div class="compare row">
                        @if(count($items))
                            <table id="compare-table" class="compare" style="border-collapse: inherit;">
                                <thead>
                                <tr>
                                    @foreach($items as $item)
                                        <td style="position: relative" width="15%" data-id="{{ $item->id }}">
                                            <a href="{{ $item->url }}">
                                                <img itemprop="image" id="product-image" title="{{ $item->name }}"
                                                     alt="{{ $item->name }}"
                                                     src="{{ $item->thumb(1) }}">
                                                <u>{{ $item->name }}</u>
                                            </a>
                                            <a data-product="58409" class="compare-remove"
                                               href="{{ route('ajax.compare-delete') }}"
                                               title="Удалить из списка сравнения"><i class="icon16 remove"></i></a>
                                        </td>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                <tr></tr>
                                <tr class="compare-name">
                                    <th>Цена:</th>
                                </tr>
                                <tr>
                                    @foreach($items as $item)
                                        <td width="15%">
                                            <span class="price nowrap">{{ $item->price }} <span
                                                        class="new-ruble">р.</span></span>
                                        </td>
                                    @endforeach
                                </tr>
                                @foreach($compare_names as $compare_val)
                                    <tr></tr>
                                    <tr class="compare-name">
                                        <th>{{ $compare_val->name }}:</th>
                                    </tr>
                                    <tr>
                                        @foreach($items as $item)
                                            <td width="15%">{{ $item->getCharByName($compare_val->name) ?: '-' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>Пусто</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
