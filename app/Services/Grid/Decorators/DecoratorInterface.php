<?php

namespace Coyote\Services\Grid\Decorators;

use Coyote\Services\Grid\Cell;

interface DecoratorInterface
{
    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell);
}
