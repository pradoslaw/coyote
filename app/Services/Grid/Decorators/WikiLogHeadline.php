<?php

namespace Coyote\Services\Grid\Decorators;

use Boduch\Grid\Cell;
use Boduch\Grid\Decorators\Decorator;

class WikiLogHeadline extends Decorator
{
    /**
     * @param Cell $cell
     */
    public function decorate(Cell $cell)
    {
        $html = $cell->getColumn()->getGrid()->getGridHelper()->getHtmlBuilder();

        $cell->setValue(
            $html->tag('strong', (string) $html->link($cell->getData()->path, $cell->getData()->title))  .
            $html->tag('p', (string) $cell->getValue(), ['class' => 'text-muted'])
        );
    }
}
