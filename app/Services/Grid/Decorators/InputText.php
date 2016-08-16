<?php

namespace Coyote\Services\Grid\Decorators;

use Coyote\Services\Grid\Cell;

class InputText extends Decorator
{
    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        $form = $cell->getColumn()->getGrid()->getFormBuilder();

        $cell->setValue($form->text($cell->getColumn()->getName() . '[]', $cell->getValue(), ['class' => 'form-control']));
    }
}
