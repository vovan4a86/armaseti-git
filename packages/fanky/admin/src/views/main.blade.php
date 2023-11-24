@extends('admin::template')

@section('content')
    <div class="box box-primary box-solid">
        <div class="box-header"><h2 class="box-title">Действия в панели</h2></div>
        <div class="box-body">
            <table class="table table-striped table-hover">
                <thead>
                <th>Дата</th>
                <th>Пользователь</th>
                <th>IP</th>
                <th>Действие</th>
                </thead>
                <tbody>
                @foreach($logs as $item)
                    <tr>
                        <td>{{ $item->created_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $item->user }}</td>
                        <td>{{ $item->ip }}</td>
                        <td>{{ $item->msg }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            {!! $logs->render() !!}
        </div>
    </div>
@stop