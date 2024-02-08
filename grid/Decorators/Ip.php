<?php

namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

class Ip extends Decorator
{
    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        // disable auto escape so we can display <a> html tag in cell
        $cell->getColumn()->setAutoescape(false);

        $cell->setValue(
            $cell->getColumn()->getGrid()->getGridHelper()->getHtmlBuilder()->tag('abbr', (string) $cell->getUnescapedValue())
        );
    }
}
