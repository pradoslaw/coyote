<?php

namespace Coyote\Services\Grid\Components;

use Boduch\Grid\Components\Button;

class CreateButton extends Button
{
    /**
     * @return \Illuminate\Support\HtmlString
     */
    public function render()
    {
        return $this->tag('a', $this->renderIcon() . $this->text, $this->attributes);
    }

    /**
     * @return \Illuminate\Support\HtmlString
     */
    protected function renderIcon()
    {
        return $this->tag('i', '', ['class' => 'fa fa-plus fa-fw']);
    }

    /**
     * @param array $attributes
     */
    protected function setDefaultAttributes(array $attributes = [])
    {
        $this->attributes = array_merge([
            'href' => $this->url,
            'class' => 'btn btn-default btn-sm',
            'data-toggle' => 'tooltip',
            'data-placement' => 'top'
        ], $attributes);
    }
}
