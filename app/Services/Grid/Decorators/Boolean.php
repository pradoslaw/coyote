<?php

namespace Coyote\Services\Grid\Decorators;

use Coyote\Services\Grid\Cell;

class Boolean extends Decorator
{
    /**
     * @var string
     */
    protected $trueLabel = 'Tak';

    /**
     * @var string
     */
    protected $falseLabel = 'Nie';

    /**
     * @return string
     */
    public function getTrueLabel()
    {
        return $this->trueLabel;
    }

    /**
     * @param string $trueLabel
     */
    public function setTrueLabel($trueLabel)
    {
        $this->trueLabel = $trueLabel;
    }

    /**
     * @return string
     */
    public function getFalseLabel()
    {
        return $this->falseLabel;
    }

    /**
     * @param string $falseLabel
     */
    public function setFalseLabel($falseLabel)
    {
        $this->falseLabel = $falseLabel;
    }

    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        $cell->setValue([0 => $this->falseLabel, 1 => $this->trueLabel][$cell->getValue()]);
    }
}
