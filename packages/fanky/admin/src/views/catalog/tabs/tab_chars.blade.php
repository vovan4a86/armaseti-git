<div class="chars">
    @foreach($product->chars as $char)
        <div class="row row-chars">
            {!! Form::hidden('chars[id][]', $char->id) !!}
            <div style="width: 50px;">
                <i class="fa fa-ellipsis-v"></i>
                <i class="fa fa-ellipsis-v"></i>
            </div>
            {!! Form::text('chars[name][]',$char->name, ['class'=>'form-control', 'placeholder' => 'Название']) !!}
            {!! Form::text('chars[value][]',$char->value, ['class'=>'form-control', 'placeholder' => 'Значение']) !!}
            <div style="width: 150px;">
                <a href="{{ route('admin.catalog.product-delete-char', ['id' => $char->id]) }}"
                   onclick="delProductChar(this, event)" class="text-red">
                    <i class="fa fa-trash"></i> Удалить</a>
            </div>
        </div>
    @endforeach
    <div class="row hidden">
        {!! Form::hidden('chars[id][]', '') !!}
        <div style="width: 50px;">
            <i class="fa fa-ellipsis-v"></i>
            <i class="fa fa-ellipsis-v"></i>
        </div>
        {!! Form::text('chars[name][]','', ['class'=>'form-control', 'placeholder' => 'Название']) !!}
        {!! Form::text('chars[value][]','', ['class'=>'form-control', 'placeholder' => 'Значение']) !!}
        <div style="width: 150px;">
            <a href="#" onclick="delProductChar(this, event)" class="text-red">
                <i class="fa fa-trash"></i> Удалить</a>
        </div>
    </div>
</div>
<a href="#" onclick="addProductChar(this, event)">Добавить</a>
<script type="text/javascript">
    $(".chars").sortable().disableSelection();
</script>
<style>
    .chars .row{
        margin: 10px;
    }
    .chars .row:nth-child(odd){
        /*background: #d2d6de !important*/
    }
    .row-chars {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .row-chars input {
        margin-right: 15px;
    }
</style>
