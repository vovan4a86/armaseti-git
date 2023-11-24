@extends('admin::template')

@section('scripts')
	<script type="text/javascript" src="/adminlte/interface_news.js"></script>
@stop

@section('page_name')
	<h1>Справочник
		<small><a href="{{ route('admin.handbook.edit') }}">Добавить статью</a></small>
	</h1>
@stop

@section('breadcrumb')
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Главная</a></li>
		<li class="active">Справочник</li>
	</ol>
@stop

@section('content')
	<div class="box box-solid">
		<div class="box-body">
			@if (count($items))
				<table class="table table-striped table-v-middle">
					<tbody id="items-list">
						@foreach ($items as $item)
							<tr data-id="{{ $item->id }}">
								<td width="40"><i class="fa fa-ellipsis-v"></i> <i class="fa fa-ellipsis-v"></i></td>
								<td width="120">
									@if($item->thumb(1))
										<img src="{{ $item->thumb(1) }}" alt="image">
									@endif
								</td>
								<td width="200"><a href="{{ route('admin.handbook.edit', [$item->id]) }}">{{ $item->name }}</a></td>
								<td><a href="{{ route('admin.handbook.edit', [$item->id]) }}">{{ mb_strimwidth(strip_tags($item->text), 0, 150, '...')  }}</a></td>
								<td width="60">
									<a class="glyphicon glyphicon-trash" href="{{ route('admin.handbook.delete', [$item->id]) }}"
									   style="font-size:20px; color:red;" title="Удалить" onclick="return newsDel(this)"></a>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>

				<script type="text/javascript">
					$("#items-list").sortable({
						update: function( event, ui ) {
							var url = "{{ route('admin.handbook.reorder') }}";
							var data = {};
							data.sorted = ui.item.closest('#items-list').sortable( "toArray", {attribute: 'data-id'} );
							sendAjax(url, data);
						}
					}).disableSelection();
				</script>
			@else
				<p>Нет статей!</p>
			@endif
		</div>
	</div>
@stop
