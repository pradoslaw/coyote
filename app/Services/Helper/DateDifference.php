<?php

namespace Coyote\Services\Helper;

use Carbon\Carbon;

readonly class DateDifference
{
    public function __construct(private string $format, private bool $diffForHumans)
    {
    }

    public function format(Carbon $earlier): string
    {
        if ($this->diffForHumans) {
            return $this->diffForHumans($earlier);
        }
        return $this->formatDate($earlier);
    }

    private function diffForHumans(Carbon $earlier): string
    {
        if ($earlier->diffInHours() < 1) {
            return $earlier->diffForHumans(null, true) . ' temu';
        }
        if ($earlier->isToday()) {
            return 'dziś, ' . $earlier->format('H:i');
        }
        if ($earlier->isYesterday()) {
            return 'wczoraj, ' . $earlier->format('H:i');
        }
        return $this->formatDate($earlier);
    }

    private function formatDate(Carbon $earlier): string
    {
        return $earlier->format($this->replace($this->format, [
            '%d' => 'd',
            '%m' => 'm',
            '%Y' => 'Y',
            '%y' => 'y',
            '%H' => 'H',
            '%M' => 'i',
            '%B' => $this->quoted($this->longMonth($earlier)),
            '%b' => $this->quoted($this->shortMonth($earlier)),
        ]));
    }

    private function replace(string $subject, array $search): string
    {
        return \str_replace(
            \array_keys($search),
            \array_values($search),
            $subject);
    }

    private function longMonth(Carbon $date): string
    {
        return $this->month($date, ['styczeń', 'luty', 'marzec', 'kwiecień', 'maj', 'czerwiec', 'lipiec', 'sierpień', 'wrzesień', 'październik', 'listopad', 'grudzień']);
    }

    private function shortMonth(Carbon $date): string
    {
        return $this->month($date, ['sty', 'lut', 'mar', 'kwi', 'maj', 'cze', 'lip', 'sier', 'wrz', 'paź', 'lis', 'gru']);
    }

    private function month(Carbon $date, array $months): mixed
    {
        return $months[$date->month - 1];
    }

    private function quoted(string $string): string
    {
        return \implode(\array_map(fn($s) => "\\$s", \str_split($string)));
    }
}
