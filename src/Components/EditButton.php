<?php

namespace Boduch\Grid\Components;

class EditButton extends RowAction
{
    /**
     * @param $data
     * @return string
     */
    public function render($data)
    {
        return (string) $this->tag(
            'a',
            (string) $this->tag('i', '', ['class' => 'fa fa-edit']),
            ['href' => $this->getActionUrl($data), 'class' => 'btn btn-default btn-xs', 'title' => 'Edytuj ten rekord']
        );
    }
}
