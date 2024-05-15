<?php
namespace Coyote\Domain\Administrator\Activity;

class Segments
{
    public function __construct(private array $values)
    {
        \uSort($this->values, fn($a, $b): int => $a[0]->compareTo($b[0]));
    }

    public function dates(): array
    {
        $dates = [];
        /**
         * @var Date $date
         * @var int $count
         */
        foreach ($this->values as [$date, $count]) {
            $dates[] = $date->toString();
        }
        return $dates;
    }

    public function peeks(): array
    {
        $peeks = [];
        foreach ($this->values as [$date, $count]) {
            $peeks[] = $count;
        }
        return $peeks;
    }
}
