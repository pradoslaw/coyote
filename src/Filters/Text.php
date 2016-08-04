<?php

namespace Boduch\Grid\Filters;

class Text extends Filter
{
    /**
     * @var string
     */
    protected $operator = FilterOperator::OPERATOR_LIKE;

    /**
     * @return \Illuminate\Support\HtmlString
     */
    public function render()
    {
        return $this->getFormBuilder()->input('text', $this->getName(), $this->getInput(), [
            'class' => 'form-control input-sm'
        ]);
    }
}
