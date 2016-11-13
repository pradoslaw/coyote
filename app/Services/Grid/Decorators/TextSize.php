<?php

namespace Coyote\Services\Grid\Decorators;

use Boduch\Grid\Cell;
use Boduch\Grid\Decorators\Decorator;

class TextSize extends Decorator
{
    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        $html = $cell->getColumn()->getGrid()->getGridHelper()->getHtmlBuilder();
        $cell->getColumn()->setAutoescape(false);

        $cell->setValue(
            $html->tag(
                'span',
                $this->size($cell->getUnescapedValue()),
                ['class' => ($cell->getUnescapedValue() >= 0 ? 'text-primary' : 'text-danger')]
            )
        );
    }

    /**
     * @param int $size
     * @return string
     */
    protected function size($size)
    {
        if (!$size) {
            return '--';
        }

        // mniejsze niz kB...
        if ($size < 1024) {
            return $size . ' b';
        }

        // mniejsze niz 1 MB
        if ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        }

        // mniejsze niz 1 GB
        if ($size < 1073741824) {
            return round($size / 1048576, 2) . ' MB';
        }
    }
}
