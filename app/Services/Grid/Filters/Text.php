<?php

namespace Coyote\Services\Grid\Filters;

class Text extends Filter
{
    public function render()
    {
        return $this->tag(
            'input',
            '',
            [
                'type' => 'text',
                'name' => $this->column->getName(),
                'class' => 'form-control input-sm',
                'value' => $this->column->getGrid()->getRequest()->input($this->column->getName())
            ]
        );
    }
}
