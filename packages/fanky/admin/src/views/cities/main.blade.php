@extends('admin::template')

@section('scripts')
    <script type="text/javascript" src="/adminlte/interface_cities.js"></script>
@stop

@section('page_name')
    <h1>Города
        <small><a href="{{ route('admin.cities.edit') }}">Добавить</a></small>
    </h1>
@stop

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Главная</a></li>
        <li class="active">Города</li>
    </ol>
@stop

@section('content')
    <div class="box box-solid">
        <div class="box-body">
            @if (count($cities))
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Название</th>
                        <th>Alias</th>
                        <th width="50"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($cities as $item)
                        <tr>
                            <td><a href="{{ route('admin.cities.edit', [$item->id]) }}">{{ $item->name }}</a></td>
                            <td>{{ $item->alias }}</td>
                            <td>
                                <a class="glyphicon glyphicon-trash" href="{{ route('admin.cities.del', [$item->id]) }}" style="font-size:20px; color:red;" title="Удалить" onclick="return cityDel(this)"></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p>Пусто!</p>
            @endif
        </div>
    </div>
@stop