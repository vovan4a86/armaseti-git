@extends('admin::template')

@section('scripts')
    <script type="text/javascript" src="/adminlte/plugins/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="/adminlte/plugins/treeview/treeview.js"></script>
    <link href="/adminlte/plugins/treeview/treeview.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="/adminlte/interface_cities.js"></script>
@stop

@section('page_name')
    <h1>
        Города
        <small>{{ $city->id ? 'Редактировать' : 'Новая' }}</small>
    </h1>
@stop

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Главная</a></li>
        <li><a href="{{ route('admin.cities') }}">Города</a></li>
        <li class="active">{{ $city->id ? 'Редактировать' : 'Новая' }}</li>
    </ol>
@stop

@section('content')
    <form action="{{ route('admin.cities.save') }}" onsubmit="return citySave(this, event)">
        <input type="hidden" name="id" value="{{ $city->id }}">

        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_1" data-toggle="tab">Параметры</a></li>
                <li><a href="#tab_2" data-toggle="tab">Выбор города из базы</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    {!! Form::groupText('name', $city->name, 'Название') !!}
                    {!! Form::groupText('from_city', $city->from_city, 'Форма (из, для)') !!}
                    {!! Form::groupText('in_city', $city->in_city, 'Форма (в, где)') !!}
                    {!! Form::groupText('alias', $city->alias, 'Alias') !!}
                    {!! Form::groupText('lat', $city->lat, 'Широта') !!}
                    {!! Form::groupText('long', $city->long, 'Долгота') !!}
                    {!! Form::groupText('title', $city->title, 'Title') !!}
                    {!! Form::groupText('description', $city->description, 'Description') !!}
                    {!! Form::groupText('keywords', $city->keywords, 'Keywords') !!}
                    {!! Form::groupRichtext('text', $city->text, 'Текст для индексной страницы города') !!}
                </div>

                <div class="tab-pane" id="tab_2">
                    <div id="tree" class="treeview"></div>
                </div>
            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </form>
    <script>
        $(document).ready(function () {
            sendAjax('{{route('admin.cities.tree', ['id' => $city->id])}}', {}, function (data) {
                if (typeof data.tree != 'undefined') {
                    initTree(data.tree);
                }
            });
        });
    </script>
    {{--<script src="https://maps.googleapis.com/maps/api/js?key={{ $api_key }}&v=3&callback=initMap"></script>--}}
@stop
