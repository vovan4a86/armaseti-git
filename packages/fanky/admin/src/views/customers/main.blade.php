@extends('admin::template')

@section('page_name')
    <h1>Каталог
        <small>Покупатели</small>
    </h1>
@stop
@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('admin') }}"><i class="fa fa-dashboard"></i> Главная</a></li>
        <li class="active">Покупатели</li>
    </ol>
@stop

@section('content')
    <div class="box box-primary box-solid">
        <div class="box-header"><h2 class="box-title">Список покупателей</h2></div>
        <form action="{{ route('admin.customers') }}">
            <div class="input-group">
                <input type="text" class="form-control" name="q" placeholder="Поиск"
                       value="{{ Request::get('q') }}">
                <span class="input-group-btn">
                    <button class="btn btn-info" type="submit">Поиск</button>
                    <a href="{{ route('admin.customers') }}" class="btn btn-danger"
                       type="button">Сброс</a>
                  </span>
            </div>
        </form>
        <div class="box-body">
            <table class="table table-striped table-hover">
                <thead>
                <th>Имя</th>
                <th>Email</th>
                <th>Телефон</th>
                <th>Компания</th>
                <th>ИНН</th>
                <th>Создан</th>
                <th>Обновлен</th>
                <th>Избранное</th>
                </thead>
                <tbody>
                @foreach($customers as $item)
                    <tr data-id="{{ $item->id }}">
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->phone }}</td>
                        <td>{{ $item->company }}</td>
                        <td>{{ $item->inn }}</td>
                        <td>{{ $item->created_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $item->updated_at->format('d.m.Y H:i') }}</td>
                        <td>
                            <input type="checkbox" name="favorite" onclick="addCustomerToFavorite(this)"
                                    {{ $item->is_favorite ? 'checked' : null}}>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            {!! Pagination::render('admin::pagination') !!}
        </div>
    </div>
@stop
