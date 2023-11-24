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
            <li class="active"><a href="#tab_1" data-toggle="tab">Параметры</a></li>
            <li><a href="#tab_2" data-toggle="tab">Текст ({{ $product->text ? 1 : 0 }})</a></li>
            <li><a href="#tab_4" data-toggle="tab">Изображения ({{ count($product->images()->get()) }})</a></li>
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
            <div class="tab-pane active" id="tab_1">

                {!! Form::groupText('name', $product->name, 'Название') !!}
                {!! Form::groupText('h1', $product->h1, 'H1') !!}
                {!! Form::groupText('article', $product->article, 'Артикул') !!}
                {!! Form::groupSelect('catalog_id', $catalogs, $product->catalog_id, 'Каталог') !!}
                {!! Form::groupText('alias', $product->alias, 'Alias') !!}
                {!! Form::groupText('title', $product->title, 'Title') !!}
                {!! Form::groupText('keywords', $product->keywords, 'keywords') !!}
                {!! Form::groupText('description', $product->description, 'description') !!}
                {!! Form::groupText('price', $product->price ?: 0, 'Цена, р (значение через точку)') !!}
                <div style="display: flex; gap: 20px">
                {!! Form::groupText('length', $product->length ?: 0, 'Длина, мм') !!}
                {!! Form::groupText('width', $product->width ?: 0, 'Ширина, мм') !!}
                {!! Form::groupText('height', $product->height ?: 0, 'Высота, мм') !!}
                </div>
                <hr>

                {!! Form::groupCheckbox('published', 1, $product->published, 'Показывать товар') !!}

            </div>
            <div class="tab-pane" id="tab_2">
                {!! Form::groupRichtext('text', $product->text, 'Текст', ['rows' => 3]) !!}
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
                    <p>Размер изображения: 616x453</p>

                    <div class="images_list">
                        @foreach ($product->images()->get() as $image)
                            @include('admin::catalog.product_image', ['image' => $image, 'active' => $product->image])
                        @endforeach
                    </div>
                @else
                    <p class="text-yellow">Изображения можно будет загрузить после сохранения товара!</p>
                @endif
            </div>
        </div>

        <div class="box-footer">
            <button type="submit" class="btn btn-primary">Сохранить</button>
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
