@extends('admin::template')

@section('scripts')
	<script src="http://code.jquery.com/ui/1.11.2/jquery-ui.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="/adminlte/interface_gallery.js"></script>
@stop

@section('page_name')
	<h1>Фото галерея</h1>
@stop

@section('breadcrumb')
	<ol class="breadcrumb">
		<li><a href="{{ route('admin') }}"><i class="fa fa-dashboard"></i> Главная</a></li>
		<li class="active">Фото галерея</li>
	</ol>
@stop

@section('content')
	<div class="row">
		<div class="col-md-4">
			<div class="box box-solid">
				<div class="box-header"><h3 class="box-title">Галереи</h3></div>

				<div class="box-body">
					<ul id="galleries" class="todo-list ui-sortable">
						@foreach ($galleries as $gallery)
							@include('admin::gallery.gallery_row', ['gallery' => $gallery])
						@endforeach
					</ul>
				</div>

				<div class="box-footer">
					<form action="{{ route('admin.gallery.gallerySave') }}" onsubmit="return galleryCreate(this)">
						<div class="input-group input-group-sm">
							<input type="text" class="form-control" name="name" value="" placeholder="Название галереи...">
							<span class="input-group-btn">
								<button class="btn btn-success btn-flat" type="submit">Создать</button>
							</span>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div id="gallery-content" class="col-md-8">

		</div>
	</div>
@stop