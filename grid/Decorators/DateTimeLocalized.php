<?php

namespace Boduch\Grid\Decorators;

use Carbon\Carbon;
use Coyote\Services\Helper\DateDifference;

class DateTimeLocalized extends DateTime
{
    protected function formatDateTime($dateTime)
    {
        $dif = new DateDifference($this->format, false);
        return $dif->format(Carbon::parse($dateTime));
    }
}
