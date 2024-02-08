<div class="form-inline">
    {{ Form::date($name . '[from]', $input['from'], ['class' => 'form-control input-sm']) }}
    {{ $separator }}
    {{ Form::date($name . '[to]', $input['to'], ['class' => 'form-control input-sm']) }}
</div>
