<?php

namespace Coyote\Services\Grid\Filters;

class Text extends Filter
{
    /**
     * @return \Illuminate\Support\HtmlString
     */
    public function render()
    {
        return $this->getFormBuilder()->input('text', $this->column->getName(), $this->getInput(), [
            'class' => 'form-control input-sm'
        ]);
    }
}
