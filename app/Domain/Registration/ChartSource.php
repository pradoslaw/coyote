<?php
namespace Coyote\Domain\Registration;

interface ChartSource
{
    public function id(): string;

    public function title(): string;

    public function inRange(HistoryRange $range): array;
}
