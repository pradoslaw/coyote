<?php

namespace Coyote\Services\Grid\Filters;

class Select extends Filter
{
    protected $options = [];

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function render()
    {
        return $this->tag(
            'select',
            (string) $this->renderOptions(),
            [
                'type' => 'select',
                'name' => $this->column->getName(),
                'class' => 'form-control input-sm',
                'value' => $this->column->getGrid()->getRequest()->input($this->column->getName())
            ]
        );
    }

    protected function renderOptions()
    {
        $html = $this->tag('option', '', ['value' => '']);

        foreach ($this->options as $key => $value) {
            $html .= $this->tag('option', $value, ['value' => $key]);
        }

        return $html;
    }


}
