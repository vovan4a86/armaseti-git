@extends('template_test')
@section('content')
    <main>
        <div class="container">
            @include('blocks.bread')
            <div class="row">
                <div class="col-sm-12">
                    <h1>Сравнение товаров: </h1>
                    <div class="compare row">
                        @if(count($items))
                            @foreach($items as $item)
                                <div class="card col-sm-3" data-id="{{ $item->id }}">
                                    <div class="card-header">
                                        <span>{{ $item->name }}</span>
                                        <a class="compare-delete" href="{{ route('ajax.compare-delete') }}">Удалить</a>
                                    </div>
                                    <div class="card-body">
                                        @if($item->chars)
                                            <ul class="prod-chars">
                                                @foreach($item->chars as $char)
                                                    <li class="prod-char">
                                                        <span style="font-weight: bold">{{ $char->name }}</span> -
                                                        {{ $char->value }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p>Пусто</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
