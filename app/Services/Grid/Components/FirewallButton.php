<?php

namespace Coyote\Services\Grid\Components;

use Boduch\Grid\Components\RowAction;

class FirewallButton extends RowAction
{
    /**
     * @return string
     */
    public function render()
    {
        return (string) $this->tag(
            'a',
            (string) $this->tag('i', '', ['class' => 'fa fa-fw fa-user-slash']),
            ['href' => $this->buildActionUrl($this->data), 'class' => 'btn btn-default btn-xs', 'title' => 'Zablokuj tego użytkownika']
        );
    }
}
