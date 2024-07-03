<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\View;

use Carbon\Carbon;
use Coyote\Domain\Administrator\View\Date;

readonly class Time
{
    public function __construct(private Carbon $now)
    {
    }

    public function dateOptional(?Carbon $date): ?Date
    {
        if ($date === null) {
            return null;
        }
        return $this->date($date);
    }

    public function date(Carbon $date): Date
    {
        return new Date($date, $this->now);
    }
}
