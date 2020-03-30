<?php

namespace Boduch\Grid\Components;

class ShowButton extends RowAction
{
    /**
     * @return string
     */
    public function render()
    {
        return (string) $this->tag(
            'a',
            (string) $this->tag('i', '', ['class' => 'fa fa-eye']),
            ['href' => $this->buildActionUrl($this->data), 'class' => 'btn btn-secondary btn-sm', 'title' => __('Show')]
        );
    }
}
