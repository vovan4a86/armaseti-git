<header class="main-header">
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">
        <div class="__container">
            <div class="navbar-header">
                <a href="{{ url('/') }}" class="navbar-brand" target="_blank">
                    <b>Admin</b>
                </a>
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#navbar-collapse" aria-expanded="false">
                    <i class="fa fa-bars"></i>
                </button>
            </div>
        @include('admin::blocks.main_menu')

        <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- User Account: style can be found in dropdown.less -->
                    <li><a href="{{ route('admin.feedbacks') }}">
                            <i class="fa fa-fw fa-bell-o"></i>
                            @if ($feedback_new = Fanky\Admin\Models\Feedback::notRead()->count())
                                <span class="label label-danger">{{ $feedback_new }}</span>
                            @endif
                        </a></li>
                    <li><a href="{{ route('admin.users') }}"><i class="fa fa-fw fa-group"></i></a></li>
                    <li><a href="{{ route('admin.pages',['sitemap' => 1]) }}" title="Обновить sitemap.xml"><i
                                    class="fa fa-fw fa-sitemap" title="Обновить sitemap.xml"></i></a></li>
                    <li><a href="{{ route('admin.pages',['clear_cache' => 1]) }}" title="Очистить кеш"><i
                                    class="fa fa-fw fa-refresh" title="Очистить кеш"></i></a></li>
{{--                    <li>--}}
{{--                        <a href="{{ route('admin.pages',['update_search' => 1]) }}" title="Обновить поисковый индекс"><i class="fa fa-fw" title="Обновить поисковый индекс">S</i></a>--}}
{{--                    </li>--}}
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <!--<img src="/adminlte/dist/img/user2-160x160.jpg" class="user-image" alt="User Image"/>-->
                            <span class="hidden-xs">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <!--<img src="/adminlte/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image" />-->
                                <p>
                                    {{ Auth::user()->name }}
                                    <small>
                                        Зарегистрирован {{ date('d.m.Y', strtotime(Auth::user()->created_at)) }}</small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="{{ route('admin.users.edit', [Auth::user()->id]) }}"
                                       class="btn btn-default btn-flat"
                                       onclick="popupAjax($(this).attr('href')); return false;">Профиль</a>
                                </div>
                                <div class="pull-right">
                                    <a href="{{ route('auth') }}" class="btn btn-default btn-flat">Выйти</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>