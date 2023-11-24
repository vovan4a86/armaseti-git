<div class="form-group">
    {{ Form::label($label, null, ['class' => 'control-label']) }}
    {!! Form::textarea($name, $value, array_merge(['rows' => 4, 'cols' => 80, 'id' => 'text'.$name, 'class' => 'form-control'], $attributes)) !!}
</div>