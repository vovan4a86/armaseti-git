@extends('admin::template')

@section('scripts')
	<script type="text/javascript" src="/adminlte/interface_users.js"></script>
@stop


@section('page_name')
	<h1>Пользователи
		<small><a href="{{ route('admin.users.edit') }}" onclick="popupAjax($(this).attr('href')); return false;">Добавить пользователя</a></small>
	</h1>
@stop

@section('breadcrumb')
	<ol class="breadcrumb">
		<li><a href="{{ route('admin') }}"><i class="fa fa-dashboard"></i> Главная</a></li>
		<li class="active">Пользователи</li>
	</ol>
@stop

@section('content')
	<div class="box box-solid">
		<div class="box-body">
			<table class="table table-striped table-v-middle">
				<thead>
					<tr>
						<th>Login</th>
						<th>Имя</th>
						<th>Роль</th>
						<th width="50"></th>
					</tr>
				</thead>
				<tbody id="users-list">
					@foreach ($users as $item)
						@include('admin::users.user_row', ['item' => $item])
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
@stop