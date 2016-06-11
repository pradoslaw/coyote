<?php

namespace Coyote\Services\Grid\Filters;

class Select extends Filter
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
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
        return $this->tag(
            'select',
            (string) $this->renderOptions($this->column->getGrid()->getRequest()->input($this->column->getName())),
            [
                'type' => 'select',
                'name' => $this->column->getName(),
                'class' => 'form-control input-sm',
                'onchange' => 'this.form.submit()'
            ]
        );
    }

    /**
     * @param string $selected
     * @return \Illuminate\Support\HtmlString|string
     */
    protected function renderOptions($selected)
    {
        $html = $this->tag('option', '', ['value' => '']);

        foreach ($this->options as $key => $value) {
            $html .= $this->tag(
                'option',
                $value,
                ['value' => $key] + (!empty($selected) && $selected == $key ? ['selected' => 'selected'] : [])
            );
        }

        return $html;
    }
}
