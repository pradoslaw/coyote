<?php

namespace Coyote\Services\Grid\Decorators;

use Boduch\Grid\Cell;
use Boduch\Grid\Decorators\Decorator;

class InputText extends Decorator
{
    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        $form = $cell->getColumn()->getGrid()->getGridHelper()->getFormBuilder();

        $cell->setValue(
            $form->text($cell->getColumn()->getName() . '[]', $cell->getValue(), ['class' => 'form-control'])
        );
    }
}
