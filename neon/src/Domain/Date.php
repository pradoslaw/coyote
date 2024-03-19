<?php
namespace Neon\Domain;

readonly class Date
{
    public function __construct(
        public int $year,
        public int $month,
        public int $day,
    )
    {
    }
}
