<?php

namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

class Placeholder extends Decorator
{
    /**
     * @var string|\Closure
     */
    protected $placeholder;

    /**
     * @param string|\Closure $placeholder
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
        if (empty($cell->getUnescapedValue())) {
            if ($this->placeholder instanceof \Closure) {
                $cell->setValue($this->placeholder->call($cell->getColumn()->getGrid(), $cell->getData()));
            } else {
                $cell->setValue($this->placeholder);
            }

            return false;
        }

        return true;
    }
}
