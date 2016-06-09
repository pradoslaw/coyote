<?php

namespace Coyote\Services\Grid\Decorators;

use Coyote\Services\Grid\Cell;

class Link extends Decorator
{
    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @param \Closure $closure
     * @return $this
     */
    public function render(\Closure $closure)
    {
        $this->closure = $closure;

        return $this;
    }

    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        $cell->setValue($this->closure->call($cell, $cell->getData()));
    }
}
