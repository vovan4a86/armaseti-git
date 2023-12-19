@section('page_name')
    <h1>Каталог
        <small>{{ $catalog->name }}</small>
    </h1>
@stop
@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('admin') }}"><i class="fa fa-dashboard"></i> Главная</a></li>
        <li><a href="{{ route('admin.catalog') }}"><i class="fa fa-list"></i> Каталог</a></li>
        @foreach($catalog->getParents(false, true) as $parent)
            <li><a href="{{ route('admin.catalog.products', [$parent->id]) }}">{{ $parent->name }}</a></li>
        @endforeach
        <li class="active">{{ $catalog->name}}</li>
    </ol>
@stop

<div class="box box-solid">
    <div class="box-body">
        <div class="panel-heading clearfix">
            <a href="{{ route('admin.catalog.productEdit', ['catalog' => $catalog->id]) }}"
               class="btn btn-sm btn-success"
               onclick="return catalogContent(this)">Добавить товар</a>
            @if(count($products))
                    <button class="btn btn-primary btn-sm" onclick="checkSelectAll()">Выделить всё</button>
                    <button class="btn btn-warning btn-sm" onclick="checkDeselectAll()">Снять выделение</button>
                    <button class="btn btn-primary btn-sm js-move-btn"
                            data-toggle="modal" data-target="#moveDialog"
                            disabled>Переместить
                    </button>
                    <button class="btn btn-danger btn-sm js-delete-btn"
                            onclick="deleteProducts(this, event)"
                            disabled>Удалить
                    </button>
                    <button class="btn btn-danger btn-sm js-delete-btn"
                            onclick="deleteProductsImage(this, event, {{ $catalog->id }})"
                            disabled>Удалить IMGs
                    </button>
            @endif
            <div class="form-group form-inline pull-right" style="float: left; margin-left: 15px;">
                <form>
                    <label> Показывать по:</label>
                    <select name="per_page" class="form-control input-sm" onchange="this.form.submit();">
                        @foreach([30,50,100,150,300,500] as $i)
                            <option value="{{ $i }}" {{ session('per_page', 30) == $i? 'selected': '' }}>{{ $i }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        <form action="{{ route('admin.catalog.search') }}">
            <div class="input-group">
                <input type="text" class="form-control" name="q" placeholder="Наименование"
                       value="{{ Request::get('q') }}">
                <span class="input-group-btn">
                    <button class="btn btn-info" type="submit">Поиск</button>
                    <a href="{{ route('admin.catalog.products', ['id' => $catalog->id]) }}" class="btn btn-danger"
                       type="button">Сброс</a>
                  </span>
            </div><!-- /input-group -->
        </form>

        @if (count($products))
            <table class="table table-striped table-v-middle">
                <thead>
                <tr>
                    <th width="40"></th>
                    <th width="100">Изображение</th>
                    <th>Название</th>
                    <th width="90" style="text-align: center">Новинка</th>
                    <th width="90" style="text-align: center">Хит</th>
                    <th>Артикул</th>
                    <th width="60" style="text-align: center">Цена</th>
                    <th width="130">Сортировка</th>
                    <th width="50"></th>
                </tr>
                </thead>
                <tbody id="catalog-products">
                @foreach ($products as $item)
                    <tr data-id="{{ $item->id }}">
                        <td>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" class="js_select" value="{{ $item->id }}"
                                           onclick="checkSelectProduct()">
                                </label>
                            </div>
                        </td>
                        <td>
                            @if ($img = $item->images()->first())
                                <img src="{{ $img->thumb(1, $catalog->alias) }}" width="100">
                            @endif
                        </td>
                        <td><a href="{{ route('admin.catalog.productEdit', [$item->id]) }}"
                               onclick="return catalogContent(this)"
                               style="{{ $item->published != 1 ? 'text-decoration:line-through;' : '' }}">
                                {{ $item->name }}
                            </a>
                        </td>
                        <td style="text-align: center">
                            <input type="checkbox" {{ $item->is_new ? 'checked' : null }}
                            onclick="toggleIsNew(this)">
                        </td>
                        <td style="text-align: center">
                            <input type="checkbox" {{ $item->is_hit ? 'checked' : null }}
                            onclick="toggleIsHit(this)">
                        </td>
                        <td>{{ $item->article }}</td>
                        <td style="text-align: center">{{ $item->price }}</td>
                        <td>
                            <form class="input-group input-group-sm"
                                  action="{{ route('admin.catalog.update-order', [$item->id]) }}"
                                  onsubmit="update_order(this, event)">
                                <input type="number" name="order" class="form-control" step="1"
                                       value="{{ $item->order }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-success btn-flat" type="submit">
                                       <span class="glyphicon glyphicon-ok"></span>
                                    </button>
                                </span>
                            </form>
                        </td>
                        <td>
                            <a class="glyphicon glyphicon-trash"
                               href="{{ route('admin.catalog.productDel', [$item->id]) }}"
                               style="font-size:20px; color:red;" title="Удалить" onclick="return productDel(this)"></a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {!! Pagination::render('admin::pagination') !!}
        @else
            <p class="text-yellow">В разделе нет товаров!</p>
        @endif
    </div>
</div>


<div id="moveDialog" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Переместить товары</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="">Выберите категорию</label>
                    {!! Form::select('move_category_id', $catalog_list, 0, ['class'=>'form-control']) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success pull-left" onclick="moveProducts(this, event)">
                    Переместить
                </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Отмена</button>
            </div>
        </div>

    </div>
</div>
