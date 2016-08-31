<div class="form-inline">
    {{ Form::date($name . '[]', $input[0], ['class' => 'form-control input-sm']) }}
    {{ $separator }}
    {{ Form::date($name . '[]', $input[1], ['class' => 'form-control input-sm']) }}
</div>
