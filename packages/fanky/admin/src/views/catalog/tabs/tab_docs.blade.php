<div class="tab-pane" id="tab_related">
    @if(!$product->id)
        <div>Добавление файлов доступно только после сохранения товара</div>
    @else
        <div class="form-group row">
            <div class="col-md-4">
                <label for="doc-file">Файл</label>
                <input id="doc-file" type="file" name="file" value=""
                       class="form-control"
                       onchange="return docAttache(this, event)">
            </div>
            <div class="col-md-4">
                <label for="article-image">Название</label>
                <input name="doc_name" type="text" placeholder="Название для отображения" class="form-control">
            </div>
            <div class="col-md-4" style="line-height: 5.6;">
                <a href="{{ route('admin.catalog.add_doc', $product->id) }}"
                   onclick="addDoc(this, event)" class="btn btn-primary add-rel">
                    Добавить документ</a>
            </div>
        </div>

        <hr>

        <table class="table table-hover table-condensed" id="doc_list">
            <thead>
            @if(count($product->docs))
                <tr>
                    <th>Иконка</th>
                    <th>Размер</th>
                    <th>Действие</th>
                    <th>Название</th>
                    <th></th>
                </tr>
            @endif
            </thead>
            <tbody>
            @foreach ($product->docs as $doc)
                @include('admin::catalog.tabs.doc_row', ['doc' => $doc])
            @endforeach
            </tbody>
        </table>


    @endif
</div>
