<div class="collapse navbar-collapse pull-left" id="navbar-collapse">
    <ul class="nav navbar-nav">
        <li><a href="{{ route('admin.pages') }}"><i class="fa fa-fw fa-sitemap"></i>
                <span class="hidden visible-lg-inline visible-xs-inline">Структура сайта</span></a></li>
        <li><a href="{{ route('admin.pages') }}"><i class="fa fa-fw fa-list"></i>
                <span class="hidden visible-lg-inline visible-xs-inline">Каталог</span></a></li>
        <li><a href="{{ route('admin.complex') }}"><i class="fa fa-fw fa-calendar"></i>
                <span class="hidden visible-lg-inline visible-xs-inline">Новости</span></a></li>
        <li><a href="{{ route('admin.gallery') }}"><i class="fa fa-fw fa-image"></i>
                <span class="hidden visible-lg-inline visible-xs-inline">Галереи</span></a></li>

        {{--<li><a href="{{ route('admin.reviews') }}"><i class="fa fa-fw fa-star"></i>--}}
        {{--<span class="hidden visible-lg-inline visible-xs-inline">Отзывы</span></a></li>--}}
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-fw fa-cogs"></i>
                <span class="hidden visible-lg-inline visible-xs-inline">Настройки</span>
                <span class="caret"></span></a>
            <ul class="dropdown-menu" role="menu">
                <li><a href="{{ route('admin.settings') }}"><i class="fa fa-fw fa-gear"></i> Настройки</a></li>
                <li><a href="{{ route('admin.redirects') }}"><i class="fa fa-fw fa-retweet"></i> Редиректы</a></li>
            </ul>
        </li>
    </ul>
</div>
