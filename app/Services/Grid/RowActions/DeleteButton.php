<?php

namespace Coyote\Services\Grid\RowActions;

class DeleteButton extends RowAction
{
    /**
     * @param $data
     * @return string
     */
    public function render($data)
    {
        return (string) $this->tag(
            'a',
            (string) $this->tag('i', '', ['class' => 'fa fa-fw fa-trash-o']),
            [
                'href' => $this->getActionUrl($data),
                'class' => 'btn btn-danger btn-xs',
                'title' => 'Usuń ten rekord',
                'data-toggle' => 'modal'
            ]
        );
    }
}
