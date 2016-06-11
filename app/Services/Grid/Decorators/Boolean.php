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
     * @return string
     */
    public function getFalseIcon()
    {
        return $this->falseIcon;
    }

    /**
     * @param string $falseIcon
     */
    public function setFalseIcon($falseIcon)
    {
        $this->falseIcon = $falseIcon;
    }

    /**
     * @return string
     */
    public function getTrueIcon()
    {
        return $this->trueIcon;
    }

    /**
     * @param string $trueIcon
     */
    public function setTrueIcon($trueIcon)
    {
        $this->trueIcon = $trueIcon;
    }

    /**
     * @param $flag
     */
    public function setTextual($flag)
    {
        $this->textual = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function isTextual()
    {
        return $this->textual;
    }

    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        $cell->setValue([0 => $this->falseLabel, 1 => $this->trueLabel][$cell->getValue()]);
    }

    protected function renderGraphical()
    {
        
    }
}
