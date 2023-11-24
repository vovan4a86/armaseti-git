<!DOCTYPE html>
<html lang="ru-RU">

@include('blocks.head')

<body x-data="{ menuOverlayIsOpen: false }" :class="menuOverlayIsOpen &amp;&amp; 'no-scroll'">

@if(!Route::is('news.item'))
<h1 class="v-hidden">{{ $h1 ?? '' }}</h1>
@endif

{!! Settings::get('counters') !!}

@include('blocks.header')

@if(!Route::is('main'))
    <div class="top-view lazy" data-bg="{{ $top_view }}">
        <div class="top-view__container container">
            <div class="top-view__title">{{ $h1 }}</div>
        </div>
    </div>
@endif
@yield('content')

@include('blocks.footer')
@include('blocks.popups')

<div class="v-hidden" id="company" itemprop="branchOf" itemscope itemtype="https://schema.org/Corporation"
     aria-hidden="true" tabindex="-1">
    {!! Settings::get('schema.org') !!}
</div>

@if(isset($admin_edit_link) && strlen($admin_edit_link))
    <div class="adminedit">
        <div class="adminedit__ico"></div>
        <a href="{{ $admin_edit_link }}" class="adminedit__name" target="_blank">Редактировать</a>
    </div>
@endif
</body>
</html>
