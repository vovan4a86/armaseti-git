@extends('admin::template')

@section('scripts')
@stop


@section('page_name')
    <h1>Редиректы
        <small><a href="{{ route('admin.redirects.edit') }}" >Добавить редирект</a></small>
    </h1>
@stop

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('admin') }}"><i class="fa fa-dashboard"></i> Главная</a></li>
        <li class="active">Редиректы</li>
    </ol>
@stop

@section('content')
    <div class="box box-solid">
        <div class="box-body">
            <table class="table table-striped table-v-middle">
                <thead>
                <tr>
                    <th>Откуда</th>
                    <th>Куда</th>
                    <th>Код</th>
                    <th width="50"></th>
                </tr>
                </thead>
                <tbody id="users-list">
                @foreach ($items as $item)
                    <tr data-id="{{ $item->id }}">
                        <td>
                            <a href="{{ route('admin.redirects.edit', [$item->id]) }}">{{ $item->from }}</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.redirects.edit', [$item->id]) }}">{{ $item->to }}</a>
                        </td>
                        <td>
                            {{ $item->code_name }}
                        </td>
                        <td>
                            <a class="glyphicon glyphicon-trash" href="{{ route('admin.redirects.delete', [$item->id]) }}" style="font-size:20px; color:red;"
                               onclick="postDelete(this, 'Действительно удалить строку?', 'tr', event)"></a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop