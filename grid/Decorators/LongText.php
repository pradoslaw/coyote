<?php
namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

class LongText extends Decorator
{
    public function decorate(Cell $cell): void
    {
        $cell->attributes->add([
            'style' => 'max-width:144px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;',
        ]);
        $cell->setValue(\str_limit($cell->getUnescapedValue(), limit:110));
    }
}
