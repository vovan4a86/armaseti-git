@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('admin') }}"><i class="fa fa-dashboard"></i> Главная</a></li>
        <li><a href="{{ route('admin.catalog') }}"><i class="fa fa-list"></i> Каталог</a></li>
        @foreach($catalog->getParents(false, true) as $parent)
            <li><a href="{{ route('admin.catalog.products', [$parent->id]) }}">{{ $parent->name }}</a></li>
        @endforeach
        <li class="active">{{ $catalog->id ? $catalog->name : 'Новый раздел' }}</li>

    </ol>
@stop
@section('page_name')
    <h1>Каталог
        <small>{{ $catalog->id ? $catalog->name : 'Новый раздел' }}</small>
    </h1>
@stop

<form action="{{ route('admin.catalog.catalogSave') }}" onsubmit="return catalogSave(this, event)">
    <input type="hidden" name="id" value="{{ $catalog->id }}">

    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1" data-toggle="tab">Параметры</a></li>
            <li><a href="#tab_2" data-toggle="tab">Тексты</a></li>
            <li class="{{ isset($tab) && $tab === 'docs' ? 'active' : '' }}"><a href="#tab_docs" data-toggle="tab">Документы
                    ({{ count($catalog->docs) }})</a></li>

            @if($catalog->parent_id == 0)
                <li><a href="#tab_3" data-toggle="tab">Фильтры раздела</a></li>
            @endif

            @if($catalog->id)
                <li class="pull-right">
                    <a href="{{ $catalog->url }}" target="_blank">Посмотреть</a>
                </li>
            @endif
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab_1">

                {!! Form::groupText('name', $catalog->name, 'Название') !!}
                {!! Form::groupText('h1', $catalog->h1, 'H1') !!}
                {!! Form::groupText('alias', $catalog->alias, 'Alias') !!}
                {!! Form::groupSelect('parent_id', ['0' => '---корень---'] + $catalogs->pluck('name', 'id')->all(),
                    $catalog->parent_id, 'Родительский раздел') !!}
                {!! Form::groupText('title', $catalog->title, 'Title') !!}
                {!! Form::groupText('keywords', $catalog->keywords, 'keywords') !!}
                {!! Form::groupText('description', $catalog->description, 'description') !!}
                {!! Form::groupText('og_title', $catalog->og_title, 'OpenGraph title') !!}
                {!! Form::groupText('og_description', $catalog->og_description, 'OpenGraph description') !!}

                <div class="box box-primary box-solid">
                    <div class="box-header with-border">
                        <span class="box-title">Шаблон автооптимизации для товаров (см. Настройки)</span>
                    </div>
                    <div class="box-body">
                        {!! Form::groupText('product_title_template', $catalog->product_title_template, 'Шаблон title') !!}
                        <div class="small">Шаблон title по умолчанию</div>
                        <div class="small">{{ $catalog->getDefaultTitleTemplate() }}</div>
                        {!! Form::groupText('product_description_template', $catalog->product_description_template, 'Шаблон description') !!}
                        <div class="small">Шаблон description по умолчанию</div>
                        <div class="small">{{ $catalog->getDefaultDescriptionTemplate() }}</div>

                        {!! Form::groupRichtext('product_text_template', $catalog->product_text_template, 'Шаблон текста') !!}
                    </div>
                    <div class="box-footer">
                        Коды замены:
                        <ul>
                            <li>{name} - название товара</li>
                            <li>{h1} - H1 товара</li>
                            <li>{lower_name} - название товара в нижнем регистре</li>
                            <li>{article} - поле товара - Артикул</li>
                            <li>{price} - поле товара - Цена</li>
                        </ul>
                    </div>
                </div>

                {!! Form::hidden('published', 0) !!}
                {!! Form::groupCheckbox('published', 1, $catalog->published, 'Показывать раздел') !!}
                @if ($catalog->parent_id == 0)
                    {!! Form::hidden('on_main', 0) !!}
                    {!! Form::groupCheckbox('on_main', 1, $catalog->on_main, 'Показывать на главной странице') !!}
                @endif

                <div class="row">
                    <div class="form-group col-xs-3" style="display: flex; column-gap: 30px;">
                        <div class="catalog-image">
                            <label for="catalog-image">Изображение раздела (.png, 130x130)
                                <i class="fa fa-question-circle fa-quest"></i>
                                <img class="question1" src="/adminlte/questions/catalog_img.png" alt="question_1">
                            </label>
                            <input id="catalog-image" type="file" name="image" value=""
                                   onchange="return catalogImageAttache(this, event)">
                            <div id="catalog-image-block">
                                @if ($catalog->image)
                                    <img class="img-polaroid"
                                         src="{{ $catalog->image_src }}" height="100"
                                         data-image="{{ $catalog->image_src }}"
                                         onclick="return popupImage($(this).data('image'))" alt="">
                                    <a class="images_del"
                                       href="{{ route('admin.catalog.catalogImageDel', [$catalog->id]) }}"
                                       onclick="return catalogImageDel(this)">
                                        <span class="glyphicon glyphicon-trash text-red"></span>
                                    </a>
                                @else
                                    <p class="text-yellow">Изображение не загружено.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-xs-3" style="display: flex; column-gap: 30px;">
                        <div class="catalog-image">
                            <label for="catalog-icon">Иконка в меню сайдбара
                                <i class="fa fa-question-circle fa-quest"></i>
                                <img class="question2" src="/adminlte/questions/catalog_side.png" alt="question_2">
                            </label>
                            <input id="catalog-icon" type="file" name="icon" value=""
                                   onchange="return catalogIconAttache(this, event)">
                            <div id="catalog-icon-block">
                                @if ($catalog->menu_icon)
                                    <img class="img-polaroid"
                                         src="{{ $catalog->icon_src }}" height="100"
                                         data-image="{{ $catalog->icon_src }}"
                                         onclick="return popupImage($(this).data('image'))" alt="">
                                    <a class="images_del"
                                       href="{{ route('admin.catalog.catalogIconDel', [$catalog->id]) }}"
                                       onclick="return catalogIconDel(this)">
                                        <span class="glyphicon glyphicon-trash text-red"></span>
                                    </a>
                                @else
                                    <p class="text-yellow">Иконка не загружена.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="tab_2">
                {!! Form::groupRichtext('text', $catalog->text, 'Основной текст') !!}
            </div>

            <div class="tab-pane {{ isset($tab) && $tab === 'docs' ? 'active' : '' }}" id="tab_docs">
                @include('admin::catalog.tabs_catalog.tab_docs')
            </div>

            @if($catalog->parent_id == 0)
                <div class="tab-pane" id="tab_3">
                    @if(count($catalogFiltersList))
                        <div style="display: flex; flex-direction: column;" class="catalog_filters">
                            @foreach($catalogFiltersList as $item)
                                <div class="filter" data-id="{{ $item->id }}">
                                    <div style="width: 50px;">
                                        <i class="fa fa-ellipsis-v"></i>
                                        <i class="fa fa-ellipsis-v"></i>
                                    </div>
                                    <div class="form-group">
                                        <input type="checkbox" name="filters[]" id="f_{{ $item->id }}"
                                               value="{{ $item->id }}" {{ $item->published ? 'checked' : '' }}
                                               onclick="updateCatalogFilter(this)">
                                        <label for="f_{{ $item->id }}"
                                               style="margin-right: 10px;">{{ $item->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <script type="text/javascript">
                            $(".catalog_filters").sortable({
                                update: function () {
                                    let url = "{{ route('admin.catalog.product-update-order-filter') }}";
                                    let data = {};
                                    data.sorted = $('.catalog_filters').sortable("toArray", {attribute: 'data-id'});
                                    sendAjax(url, data);
                                },
                            }).disableSelection();
                        </script>
                        <style>
                            .filter {
                                display: flex;
                            }
                        </style>
                    @else
                        <p>Нет фильтров</p>
                    @endif
                </div>
            @endif

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </div>
</form>
