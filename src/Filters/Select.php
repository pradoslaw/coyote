<?php

namespace Boduch\Grid\Filters;

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
     * @var bool
     */
    protected $autoSubmit = true;

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
     * @return boolean
     */
    public function isAutoSubmit(): bool
    {
        return $this->autoSubmit;
    }

    /**
     * @param boolean $flag
     */
    public function setAutoSubmit(bool $flag)
    {
        $this->autoSubmit = $flag;
    }

    /**
     * @return \Illuminate\Support\HtmlString
     */
    public function render()
    {
        return $this->getFormBuilder()->select($this->getName(), $this->options, $this->getInput(),
            array_merge(
                ['class' => 'form-control input-sm', 'onchange' => $this->autoSubmit ? 'this.form.submit()' : ''],
                $this->attr
            )
        );
    }
}
