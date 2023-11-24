<div style="width: 400px">
    <form data-id="{{ $param->id }}" action="{{ route('admin.catalog.save_param', $param->id) }}"
          onsubmit="saveParam(this, event)">
        {!! Form::groupText('name', $param->name) !!}
        {!! Form::groupText('alias', $param->alias) !!}
        {!! Form::groupText('measure', $param->measure) !!}
        <input type="submit" value="Сохранить" class="btn btn-primary" />
    </form>
</div>
