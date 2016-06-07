<?php

namespace Coyote\Services\Grid\Columns;

class Boolean extends Column
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
     * @param mixed $value
     * @return mixed
     */
    protected function setupValue($value)
    {
        return [0 => $this->falseLabel, 1 => $this->trueLabel][$value];
    }
}
