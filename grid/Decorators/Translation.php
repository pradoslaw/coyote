<?php

namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;

class Translation extends Decorator
{
    /**
     * @var string
     */
    protected $domain;

    /**
     * @param string $domain
     */
    public function __construct($domain = '')
    {
        if (!empty($domain)) {
            $this->domain = $domain . '.';
        }
    }

    /**
     * @param Cell $cell
     */
    public function decorate(Cell $cell)
    {
        $cell->setValue(trans($this->domain . $cell->getValue()));
    }
}
