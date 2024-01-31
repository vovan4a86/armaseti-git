@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('admin') }}"><i class="fa fa-dashboard"></i> Главная</a></li>
        <li><a href="{{ route('admin.catalog') }}"><i class="fa fa-list"></i> Каталог</a></li>
        @foreach($product->getParents(false, true) as $parent)
            <li><a href="{{ route('admin.catalog.products', [$parent->id]) }}">{{ $parent->name }}</a></li>
        @endforeach
        <li class="active">{{ $product->id ? $product->name : 'Новый товар' }}</li>
    </ol>
@stop
@section('page_name')
    <h1>Каталог
        <small style="max-width: 350px;">{{ $product->id ? $product->name : 'Новый товар' }}</small>
    </h1>
@stop

<form action="{{ route('admin.catalog.productSave') }}" onsubmit="return productSave(this, event)">
    {!! Form::hidden('id', $product->id) !!}

    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="{{ isset($tab) ? '' : 'active' }}"><a href="#tab_1" data-toggle="tab">Параметры</a></li>
            <li><a href="#tab_2" data-toggle="tab">Текст ({{ $product->text ? 1 : 0 }})</a></li>
            <li><a href="#tab_3" data-toggle="tab">Характеристики ({{ count($product->chars) }})</a></li>
            <li class="{{ isset($tab) && $tab === 'docs' ? 'active' : '' }}"><a href="#tab_docs" data-toggle="tab">Документы
                    ({{ count($product->docs) }})</a></li>
            <li><a href="#tab_4" data-toggle="tab">Изображения ({{ count($product->images) }})</a></li>
            <li class="pull-right">
                <a href="{{ route('admin.catalog.products', [$product->catalog_id]) }}"
                   onclick="return catalogContent(this)">К списку товаров</a>
            </li>
            @if($product->id)
                <li class="pull-right">
                    <a href="{{ $product->url }}" target="_blank">Посмотреть</a>
                </li>
            @endif
        </ul>
        <div class="tab-content">
            <div class="tab-pane {{ isset($tab) ? '' : 'active' }}" id="tab_1">

                {!! Form::groupText('name', $product->name, 'Название') !!}
                {!! Form::groupText('h1', $product->h1, 'H1') !!}
                {!! Form::groupText('alias', $product->alias, 'Alias', ['disabled' => true]) !!}
                {!! Form::groupText('article', $product->article, 'Артикул', ['disabled' => true]) !!}
                {!! Form::groupSelect('catalog_id', $catalogs, $product->catalog_id, 'Каталог') !!}
                {!! Form::groupText('title', $product->title, 'Title') !!}
                {!! Form::groupText('keywords', $product->keywords, 'keywords') !!}
                {!! Form::groupText('description', $product->description, 'description') !!}

                <div style="display: flex; gap: 20px">
                    {!! Form::groupText('price', $product->price ?: 0, 'Цена') !!}
                    {!! Form::groupText('is_discount', $product->is_discount, 'Скидка') !!}
                    {{--                    {!! Form::groupText('price', $product->product_count ?: 0, 'Наличие, шт') !!}--}}
                </div>

                {!! Form::groupCheckbox('is_new', 1, $product->is_new, 'Новинка') !!}
                {!! Form::groupCheckbox('is_hit', 1, $product->is_hit, 'Хит') !!}

                <hr>
                {!! Form::groupCheckbox('published', 1, $product->published, 'Показывать товар') !!}
                {!! Form::groupCheckbox('in_stock', 1, $product->in_stock, 'Наличие') !!}

            </div>

            <div class="tab-pane" id="tab_2">
                {!! Form::groupRichtext('text', $product->text, 'Текст', ['rows' => 3]) !!}
            </div>

            <div class="tab-pane" id="tab_3">
                @if ($product->id)
                    @include('admin::catalog.tabs.tab_chars')
                @else
                    <p>Добавить характеристики можно после сохранения товара.</p>
                @endif
            </div>

            <div class="tab-pane {{ isset($tab) && $tab === 'docs' ? 'active' : '' }}" id="tab_docs">
                @include('admin::catalog.tabs.tab_docs')
            </div>

            <div class="tab-pane" id="tab_4">
                <input id="product-image" type="hidden" name="image" value="{{ $product->image }}">
                @if ($product->id)
                    <div class="form-group">
                        <label class="btn btn-success">
                            <input id="offer_imag_upload" type="file" multiple
                                   data-url="{{ route('admin.catalog.productImageUpload', $product->id) }}"
                                   style="display:none;" onchange="productImageUpload(this, event)">
                            Загрузить изображения
                        </label>
                    </div>

                    <div class="images_list">
                        @foreach ($product->images as $image)
                            @include('admin::catalog.product_image', ['image' => $image, 'alias' => $product->catalog->alias])
                        @endforeach
                    </div>
                @else
                    <p class="text-yellow">Изображения можно будет загрузить после сохранения товара!</p>
                @endif
            </div>
        </div>

        <div class="box-footer">
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a class="pull-right" href="{{ $product->parse_url }}" target="_blank">
                <i class="fa fa-external-link" style="vertical-align: middle"></i> Страница источника
            </a>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(".images_list").sortable({
        update: function (event, ui) {
            var url = "{{ route('admin.catalog.productImageOrder') }}";
            var data = {};
            data.sorted = $('.images_list').sortable("toArray", {attribute: 'data-id'});
            sendAjax(url, data);
            //console.log(data);
        },
    }).disableSelection();
</script>
