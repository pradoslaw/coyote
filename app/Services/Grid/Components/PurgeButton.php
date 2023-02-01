<?php

namespace Coyote\Services\Grid\Components;

use Boduch\Grid\Components\RowAction;

class PurgeButton extends RowAction
{
    /**
     * @return string
     */
    public function render()
    {
        return (string) $this->tag(
            'a',
            (string) $this->tag('i', '', ['class' => 'fa fa-fw fa-fire']),
            ['href' => $this->buildActionUrl($this->data), 'class' => 'btn btn-default btn-xs', 'title' => 'Usuń tego użytkownika z powierzchni ziemi']
        );
    }
}
