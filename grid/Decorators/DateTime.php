<?php

namespace Boduch\Grid\Decorators;

use Carbon\Carbon;
use Boduch\Grid\Cell;

class DateTime extends Decorator
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
        if ($cell->getUnescapedValue()) {
            $cell->setValue($this->formatDateTime($cell->getUnescapedValue()));
        }
    }

    /**
     * @param $dateTime
     * @return string
     */
    protected function formatDateTime($dateTime)
    {
        return Carbon::parse($dateTime)->format($this->format);
    }
}
