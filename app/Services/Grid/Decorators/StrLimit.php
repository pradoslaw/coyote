<?php

namespace Coyote\Services\Grid\Decorators;

use Coyote\Services\Grid\Cell;

class StrLimit extends Decorator
{
    /**
     * @var int
     */
    protected $limit;

    /**
     * @param int $limit
     */
    public function __construct($limit = 100)
    {
        $this->limit = $limit;
    }

    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        $cell->setValue(str_limit($cell->getValue(), $this->limit));
    }
}
