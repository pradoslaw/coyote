<?php

namespace Coyote\Services\Grid\Filters;

class Select extends Filter
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $emptyValue = '--';

    /**
     * @var string
     */
    protected $operator = FilterOperator::OPERATOR_EQ;

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = ['' => $this->emptyValue] + $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return \Illuminate\Support\HtmlString
     */
    public function render()
    {
        return $this->getFormBuilder()->select($this->column->getName(), $this->options, $this->getInput(), [
            'class' => 'form-control input-sm',
            'onchange' => 'this.form.submit()'
        ]);
    }
}
