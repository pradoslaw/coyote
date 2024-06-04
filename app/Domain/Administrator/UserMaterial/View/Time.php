<?php
namespace Coyote\Domain\Administrator\UserMaterial\View;

use Carbon\Carbon;
use Coyote\Domain\Administrator\View\Date;

readonly class Time
{
    public function __construct(private Carbon $now)
    {
    }

    public function format(Carbon $carbon): string
    {
        $date = new Date($carbon, Carbon::now());
        return "$date";
    }

    public function ago(Carbon $time): string
    {
        $date = new Date($time, $this->now);
        return $date->timeAgo();
    }
}
