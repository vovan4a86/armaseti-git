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
                           value="{{ $item->id }}" {{ $item->published ? 'checked' : '' }}>
                    <label for="f_{{ $item->id }}"
                           style="margin-right: 10px;">{{ $item->name }}</label>
                    <a class="filter-edit"
                       href="{{ route('admin.catalog.catalog-filter-edit', [$catalog->id]) }}"
                       onclick="return catalogFilterEdit(this, event)">
                        <span class="glyphicon glyphicon-edit"></span>
                    </a>
                    <a class="filter-delete"
                       href="{{ route('admin.catalog.catalog-filter-delete', [$catalog->id]) }}"
                       onclick="return catalogFilterDelete(this, event)">
                        <span class="glyphicon glyphicon-trash text-red"></span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    <script type="text/javascript">
        $(".catalog_filters").sortable({
            update: function () {
                let url = "{{ route('admin.catalog.catalog-filter-update-order') }}";
                let data = {};
                data.sorted = $('.catalog_filters').sortable("toArray", {attribute: 'data-id'});
                data.catalog_id = $('input[name=id]').val();
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
