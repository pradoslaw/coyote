<?php

namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

class Placeholder extends Decorator
{
    /**
     * @var string
     */
    protected $placeholder;

    /**
     * @param string $placeholder
     */
    public function __construct($placeholder)
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @param Cell $cell
     * @return bool
     */
    public function decorate(Cell $cell)
    {
        if (empty($cell->getValue())) {
            $cell->setValue($this->placeholder);

            return false;
        }

        return true;
    }
}
