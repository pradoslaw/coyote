<?php

namespace Boduch\Grid\Decorators;

use Carbon\Carbon;

class DateTimeLocalized extends DateTime
{
    /**
     * @param $dateTime
     * @return string
     */
    protected function formatDateTime($dateTime)
    {
        return Carbon::parse($dateTime)->formatLocalized($this->format);
    }
}
