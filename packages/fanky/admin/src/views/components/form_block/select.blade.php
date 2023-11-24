<div class="form-group">
    {{ Form::label($label, null, ['class' => 'control-label']) }}
    {!! Form::select($name, $list, $value, array_merge(['class' => 'form-control'], $attributes)) !!}
</div>