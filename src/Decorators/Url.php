<?php

namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

class Url extends Decorator
{
    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        $url = (string) $cell->getValue();
        $cell->setValue(
            $cell->getColumn()->getGrid()->getGridHelper()->getHtmlBuilder()->tag('a', $url, ['href' => $url])
        );
    }
}
