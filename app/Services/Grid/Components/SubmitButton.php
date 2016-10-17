<?php

namespace Coyote\Services\Grid\Components;

use Boduch\Grid\Components\Button;

class SubmitButton extends Button
{
    /**
     * @return \Illuminate\Support\HtmlString
     */
    public function render()
    {
        return $this->tag('button', $this->text, $this->attributes);
    }

    /**
     * @param array $attributes
     */
    protected function setDefaultAttributes(array $attributes = [])
    {
        $this->attributes = array_merge([
            'type' => 'submit',
            'class' => 'btn btn-primary',
        ], $attributes);
    }
}
