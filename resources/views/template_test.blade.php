<!DOCTYPE html>
<html lang="ru-RU">

<head>
    <meta charset="utf-8">
    {!! SEOMeta::generate() !!}
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <meta name="apple-mobile-web-app-title" content="name">
    <meta name="application-name" content="name">
    <meta name="cmsmagazine" content="18db2cabdd3bf9ea4cbca88401295164">
    <meta name="author" content="Fanky.ru">
    <meta name="msapplication-TileColor" content="#ffc40d">
    <meta name="msapplication-config" content="/static/images/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Люкскрафт">
    {!! OpenGraph::generate() !!}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('static/css/all.css') }}" media="all">
    <script src="{{ mix('static/js/all.js') }}" defer></script>
</head>

<body>

{!! Settings::get('counters') !!}

<header>
    <div class="row">
        <div class="col-sm-6">
            <h1 class="text-center">Header</h1>
        </div>
        <div class="col-sm-3">
            <div class="compare-block">
                <span>Сравнение: </span>
                <span class="compare">0</span>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="favorite-block">
                <span>Избранное: </span>
                <span class="favorite">{{ count($favorites) }}</span>
            </div>
        </div>
    </div>
</header>
<hr>

@yield('content')

<hr>
<footer>
    <h1 class="text-center">Footer</h1>
</footer>

{{--<div class="v-hidden" id="company" itemprop="branchOf" itemscope itemtype="https://schema.org/Corporation"--}}
{{--     aria-hidden="true" tabindex="-1">--}}
{{--    {!! Settings::get('schema.org') !!}--}}
{{--</div>--}}

@if(isset($admin_edit_link) && strlen($admin_edit_link))
    <div class="adminedit">
        <div class="adminedit__ico"></div>
        <a href="{{ $admin_edit_link }}" class="adminedit__name" target="_blank">Редактировать</a>
    </div>
@endif
</body>
</html>
