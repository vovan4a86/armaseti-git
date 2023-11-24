@extends('admin::template')

@section('head')
	<link href="/adminlte/plugins/jstree/dist/themes/default/style.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('scripts')
	<script src="http://code.jquery.com/ui/1.11.2/jquery-ui.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="/adminlte/plugins/ckeditor/ckeditor.js"></script>
	<script type="text/javascript" src="/adminlte/plugins/jstree/dist/jstree.min.js"></script>
    <script type="text/javascript" src="/adminlte/interface_pages.js"></script>
	<script type="text/javascript" src="/adminlte/interface_settings.js"></script>
	<script type="text/javascript" src="/adminlte/interface_gallery.js"></script>
@stop

@section('page_name')
	<h1>Структура сайта</h1>
@stop

@section('breadcrumb')
	<ol class="breadcrumb">
		<li><a href="{{ route('admin') }}"><i class="fa fa-dashboard"></i> Главная</a></li>
		<li class="active">Структура сайта</li>
	</ol>
@stop

@section('content')
	<div class="row">
        <iframe id="lfm" src="/admin/laravel-filemanager?type=images&langCode=ru" style="width: 100%; overflow: hidden; border: none;"></iframe>
        <script type="application/javascript">

            function resizeIframe(iframeID) {
                var FramePageHeight = document.getElementById('content-wrapper').scrollHeight - 200;
                document.getElementById(iframeID).height=FramePageHeight;
                console.log(FramePageHeight);
            }

            window.addEventListener('DOMContentLoaded', function(e) {
                resizeIframe( 'lfm' );
            } );
        </script>
	</div>
@stop