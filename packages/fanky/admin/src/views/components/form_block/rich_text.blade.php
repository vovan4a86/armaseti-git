<div class="form-group">
    {{ Form::label($label, null, ['class' => 'control-label']) }}
    {!! Form::textarea($name, $value, array_merge(['rows' => 10, 'cols' => 80, 'id' => 'text'.$name], $attributes)) !!}
    <script type="text/javascript">startCkeditor('text{{$name}}');</script>
</div>