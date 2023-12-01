<input id="product-doc" type="hidden" name="document" value="ИКОНКА ФАЙЛА">
@if ($product->id)
    <div class="form-group">
        <label class="btn btn-success">
            <input id="doc_upload" type="file" multiple
                   data-url="{{ route('admin.catalog.product-add-doc', $product->id) }}"
                   accept=".pdf"
                   style="display:none;" onchange="productDocUpload(this, event)">
            Загрузить файлы
        </label>
        <p>Файлы: .pdf</p>
    </div>

    <div class="docs_list">
        @foreach ($product->docs as $doc)
            @include('admin::catalog.tabs.doc_row', ['doc' => $doc])
        @endforeach
    </div>

    <style>
        .docs_del {
            top: 28px!important;
        }
        .docs_item{
            position: relative;
            display: inline-block;
        }
        .docs_item img{
            max-width: 400px;
            max-height: 113px;
        }
        .docs_item img.active{
            border: 1px solid green !important;
        }
        .docs_item .docs_del {
            display:none;
            position: absolute;
            top: 11px;
            right: 11px;
            padding: 3px 6px;
            background: rgba(255,0,0,.5);
            color: #fff;
        }
        .docs_item:hover .docs_del{
            display: block !important;
        }
        .docs_item .docs_edit {
            display:none;
            position: absolute;
            bottom: 11px;
            right: 11px;
            padding: 3px 6px;
            background: rgba(255,163,45,.7);
            color: #fff;
        }
        .docs_item:hover .docs_edit{
            display: block !important;
        }
    </style>

    <script type="text/javascript">
        $(".docs_list").sortable({
            update: function () {
                let url = "{{ route('admin.catalog.product-update-order-doc') }}";
                let data = {};
                data.sorted = $('.docs_list').sortable("toArray", {attribute: 'data-id'});
                sendAjax(url, data);
            },
        }).disableSelection();
    </script>
@else
    <p class="text-yellow">Документы можно будет загрузить после сохранения товара!</p>
@endif
