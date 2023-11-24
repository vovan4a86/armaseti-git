<div class="params">
    @foreach($product->chars as $param)
        <div class="row row-params">
            {!! Form::hidden('params[id][]', $param->id) !!}
            <div style="width: 50px;">
                <i class="fa fa-ellipsis-v"></i>
                <i class="fa fa-ellipsis-v"></i>
            </div>
            {!! Form::text('params[name][]',$param->name, ['class'=>'form-control', 'placeholder' => 'Название']) !!}
            {!! Form::text('params[value][]',$param->value, ['class'=>'form-control', 'placeholder' => 'Значение']) !!}
            <div style="width: 150px;">
                <a href="#" onclick="delProductParam(this, event)" class="text-red">
                    <i class="fa fa-trash"></i>Удалить</a>
            </div>
        </div>
    @endforeach
    <div class="row hidden">
        {!! Form::hidden('params[id][]', '') !!}
        <div style="width: 50px;">
            <i class="fa fa-ellipsis-v"></i>
            <i class="fa fa-ellipsis-v"></i>
        </div>
        {!! Form::text('params[name][]','', ['class'=>'form-control', 'placeholder' => 'Название']) !!}
        {!! Form::text('params[value][]','', ['class'=>'form-control', 'placeholder' => 'Значение']) !!}
        <div style="width: 150px;">
            <a href="#" onclick="delProductParam(this, event)" class="text-red">
                <i class="fa fa-trash"></i>Удалить</a>
        </div>
    </div>
</div>
<a href="#" onclick="addProductParam(this, event)">Добавить</a>
<script type="text/javascript">
    $(".params").sortable().disableSelection();
</script>
<style>
    .params .row{
        margin: 10px;
        /*padding: 10px;*/
    }
    .params .row:nth-child(odd){
        /*background: #d2d6de !important*/
    }
    .row-params {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .row-params input {
        margin-right: 15px;
    }
</style>
