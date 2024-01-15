@extends('admin::template')

@section('scripts')
    <script type="text/javascript" src="/adminlte/interface_orders.js"></script>
@stop

@section('page_name')
    <h1>Заказ № {{ $order->id }}</h1>
@stop

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Главная</a></li>
        <li><a href="{{ route('admin.orders') }}">Заказы</a></li>
        <li class="active">Заказ № {{ $order->id }}</li>
    </ol>
@stop

@section('content')
    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-4 text-bold">Дата заказа</div>
                        <div class="col-md-8">{{ $order->dateFormat() }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-bold">Имя</div>
                        <div class="col-md-8">{{ $order->name }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-bold">Телефон</div>
                        <div class="col-md-8">{{ $order->phone }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-bold">Email</div>
                        <div class="col-md-8"><a href="mailto:{{ $order->email }}">{{ $order->email }}</a></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-4 text-bold">Сообщение</div>
                        <div class="col-md-8">{{ $order->message }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-bold">Прикрепленный файл</div>
                        <div class="col-md-8">
                            @if($order->file)
                                <a target="_blanc"
                                   href="{{ \Fanky\Admin\Models\Order::UPLOAD_URL . $order->file }}">Смотреть</a>
                            @else
                                <span>Нет</span>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-bold">Реквизиты</div>
                        <div class="col-md-8">
                            @if($order->details)
                                <a target="_blanc"
                                   href="{{ \Fanky\Admin\Models\Order::UPLOAD_URL . $order->details }}">Смотреть</a>
                            @else
                                <span>Нет</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box box-solid">
        <div class="box-body">
            @if (count($items))
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th style="text-align: center;">N</th>
                        <th>Товар</th>
                        <th style="text-align: center;">Количество</th>
                        <th style="text-align: left">Цена, руб</th>
                        <th style="text-align: left">Стоимость, руб</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td width="10" style="text-align: center;">{{ $loop->iteration }}</td>
                            <td><a target="_blank"
                                   href="{{ route('admin.catalog.productEdit', [$item->id]) }}">{{ $item->name }}</a>
                            </td>
                            <td style="text-align: center;">{{ $item->pivot->count }}</td>
                            <td>{{ number_format($item->price, 0, '', ' ') }}</td>
                            <td>{{ number_format($item->pivot->price, 0, '', ' ') }} </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th>Итого:</th>
                        <th style="text-align: center;">{{ $all_count }}</th>
                        <th style="text-align: left">{{ '' }}</th>
                        <th style="text-align: left">{{ number_format($all_sum, 0, '', ' ') }}</th>
                    </tr>
                    </tfoot>
                </table>
            @else
                <p>Нет товаров в заказе!</p>
            @endif
        </div>
    </div>
@stop
