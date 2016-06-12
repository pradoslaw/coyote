<?php

namespace Coyote\Services\Grid\Decorators;

use Carbon\Carbon;
use Coyote\Services\Grid\Cell;

class DateTimeFormat extends Decorator
{
    /**
     * @var string
     */
    protected $format;

    /**
     * @param string $format
     */
    public function __construct(string $format)
    {
        $this->format = $format;
    }

    /**
     * @param Cell $cell
     * @return void
     */
    public function decorate(Cell $cell)
    {
        if ($cell->getValue()) {
            $cell->setValue(Carbon::parse($cell->getValue())->format($this->format));
        }
    }
}
