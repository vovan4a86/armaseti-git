@extends('admin::template')

@section('scripts')
	<script type="text/javascript" src="/adminlte/interface_feedback.js"></script>
@stop

@section('page_name')
	<h1>Обратная связь</h1>
@stop

@section('breadcrumb')
	<ol class="breadcrumb">
		<li><a href="{{ route('admin') }}"><i class="fa fa-dashboard"></i> Главная</a></li>
		<li class="active">Обратная связь</li>
	</ol>
@stop

@section('content')
	<div class="box box-solid">
		<div class="box-body">
			@if (count($feedbacks))
				<div>
					<a class="btn btn-link" href="#" onclick="feedbackSelectAll(this, event)">Выделить все</a>
					<a class="btn btn-link" href="#" onclick="feedbackSelectNew(this, event)">Выделить новые</a>
					<a class="btn btn-link" href="#" onclick="feedbackUnSelectAll(this, event)">Снять выделение</a>
					<b style="margin-left:20px;">Действия</b>:
					<a class="btn btn-link" href="{{ route('admin.feedbacks.read') }}" onclick="feedbackReadSelect(this, event)">Отметить как прочитанные</a>
					<a class="btn btn-link" href="{{ route('admin.feedbacks.del') }}" onclick="feedbackDelSelect(this, event)">Удалить</a>
				</div>

				<form id="feedbacks-form">
					<table class="table table-striped table-v-middle">
						<tr>
							<th width="40"></th>
							<th width="130">Дата</th>
							<th width="200">Тип</th>
							<th>Данные</th>
							<th width="50"></th>
						</tr>
						@foreach ($feedbacks as $item)
							<tr class="{{ !$item->read_at ? 'bg-gray fb-new' : '' }}">
								<td align="center">
									<input type="checkbox" name="id[]" value="{{ $item->id }}">
								</td>
								<td>{{ $item->created_at->format('d.m.Y - H:i') }}</td>
								<td>{{ $item->type_name }}</td>
								<td>{!! $item->data_info !!}</td>
								<td>
									<a class="glyphicon glyphicon-trash" href="{{ route('admin.feedbacks.del', [$item->id]) }}" style="font-size:20px; color:red;" onclick="feedbackDel(this, event)"></a>
								</td>
							</tr>
						@endforeach
					</table>
				</form>
				{!! Pagination::render('admin::pagination') !!}
			@else
				<p class="text-yellow">Нет запросов!</p>
			@endif
		</div>
	</div>
@stop