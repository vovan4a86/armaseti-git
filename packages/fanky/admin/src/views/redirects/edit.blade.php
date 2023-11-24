@extends('admin::template')

@section('scripts')
@endsection


@section('page_name')
    <h1>Редиректы
        <small><a href="{{ route('admin.redirects.edit') }}" >Добавить редирект</a></small>
    </h1>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('admin') }}"><i class="fa fa-dashboard"></i> Главная</a></li>
        <li class="active">Редиректы</li>
    </ol>
@endsection

@section('content')

    <form action="{{ route('admin.redirects.save',['id' => $item->id]) }}" style="width:600px;" method="post">
	<input type="hidden" name="id" value="{{ $item->id }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
	<div class="form-group">
		<label for="user-name">Откуда</label>
		<input id="user-name" class="form-control" type="text" name="from" value="{{ $item->from }}">
	</div>

	<div class="form-group">
		<label for="user-email">Куда</label>
		<input id="user-email" class="form-control" type="text" name="to" value="{{ $item->to }}">
	</div>

	<div class="form-group">
		<label for="user-code">Код</label>
        {!! Form::select('code', \Fanky\Admin\Models\Redirect::$codes, $item->code, [
            'class' => 'form-control',
        ]) !!}
	</div>

	<button class="btn btn-primary" type="submit">Сохранить</button>
</form>
@endsection