<!DOCTYPE html>
<html lang="ru-RU">

@include('blocks.head')

<body class="no-scroll" x-data="{ catalogIsOpen: false, mobNavIsOpen: false }" :class="mobNavIsOpen &amp;&amp; 'no-scroll'">

@if(isset($h1))
    <h1 class="v-hidden">{{ $h1 }}</h1>
@endif
<div class="preloader">
    <div class="preloader__loader"></div>
    <script type="text/javascript">
        const preloader = document.querySelector('.preloader');
        const body = document.querySelector('body');
        if (preloader) {
            window.addEventListener('load', () => {
                body.classList.remove('no-scroll');
                preloader.classList.add('unactive');
            });
        }
    </script>
</div>

{!! Settings::get('counters') !!}

@include('blocks.header')
@include('blocks.mobile')

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
