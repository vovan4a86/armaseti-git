@extends('admin::template')

@section('scripts')
	<script type="text/javascript" src="/adminlte/interface_customers.js"></script>
@stop


@section('page_name')
	<h1>Пользователи
		<small>
			<a href="{{ route('admin.customers.edit') }}" onclick="popupAjax($(this).attr('href')); return false;">
				Добавить покупателя
			</a>
		</small>
	</h1>
@stop

@section('breadcrumb')
	<ol class="breadcrumb">
		<li><a href="{{ route('admin') }}"><i class="fa fa-dashboard"></i> Главная</a></li>
		<li class="active">Покупатели</li>
	</ol>
@stop

@section('content')
	<div class="box box-solid">
		<div class="box-body">
			<table class="table table-striped table-v-middle">
				<thead>
					<tr>
						<th>Email</th>
						<th>Имя</th>
						<th>Телефон</th>
						<th width="50"></th>
					</tr>
				</thead>
				<tbody id="users-list">
					@foreach ($customers as $item)
						@include('admin::customers.customer_row', ['item' => $item])
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
@stop
