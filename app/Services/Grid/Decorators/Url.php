<?php

namespace Coyote\Services\Grid\Decorators;

use Coyote\Services\Grid\Cell;

class Url extends Decorator
{
    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        $url = (string) $cell->getValue();
        $cell->setValue($cell->getColumn()->getGrid()->getHtmlBuilder()->tag('a', $url, ['href' => $url]));
    }
}
