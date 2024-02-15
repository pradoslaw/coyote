<?php

namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

class Boolean extends Decorator
{
    /**
     * @var string
     */
    protected $trueLabel = 'Yes';

    /**
     * @var string
     */
    protected $falseLabel = 'No';

    /**
     * @var string
     */
    protected $trueIcon = 'fa-check-circle';

    /**
     * @var string
     */
    protected $falseIcon = 'fa-minus';

    /**
     * @var bool
     */
    protected $textual = false;

    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        if ($this->textual) {
            $this->renderTextual($cell);
        } else {
            $this->renderGraphical($cell);
        }
    }

    /**
     * @param Cell $cell
     */
    protected function renderGraphical(Cell $cell)
    {
        $class = [0 => $this->falseIcon, 1 => $this->trueIcon][$cell->getUnescapedValue()];
        // disable auto escape so we can display <a> html tag in cell
        $cell->getColumn()->setAutoescape(false);

        $cell->setValue(
            $cell->getColumn()->getGrid()->getGridHelper()->getHtmlBuilder()->tag('i', '', ['class' => "fa $class"])
        );
    }

    /**
     * @param Cell $cell
     */
    protected function renderTextual(Cell $cell)
    {
        $cell->setValue([0 => $this->falseLabel, 1 => $this->trueLabel][$cell->getUnescapedValue()]);
    }
}
