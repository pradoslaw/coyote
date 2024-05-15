<?php
namespace Coyote\Domain\Administrator\Activity;

use Carbon\Carbon;

class Date implements \JsonSerializable
{
    private Carbon $date;

    public function __construct(private string $dateString)
    {
        $this->date = new Carbon($dateString);
    }

    public function jsonSerialize(): string
    {
        return $this->dateString;
    }

    public function compareTo(Date $other): int
    {
        return $this->hashCode($this) - $this->hashCode($other);
    }

    private function hashCode(Date $date): int
    {
        return $date->date->year * 32 + $date->date->month;
    }

    public function toString(): string
    {
        $months = ['styczeń', 'luty', 'marzec', 'kwiecień', 'maj', 'czerwiec', 'lipiec', 'sierpień', 'wrzesień', 'październik', 'listopad', 'grudzień'];
        return $months[$this->date->month - 1] . ' ' . $this->date->format('Y');
    }
}
