<?php
namespace Coyote\Domain\Administrator\Activity;

use Carbon\Carbon;

class Date implements \JsonSerializable
{
    private Carbon $date;

    public function __construct(private string $dateString, private string $scale)
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
        if ($this->scale === 'hour') {
            return $this->genitiveMonths($this->date->format('d F, H:i'));
        }
        if ($this->scale === 'day') {
            return $this->genitiveMonths($this->date->format('d F Y'));
        }
        if ($this->scale == 'month') {
            return $this->nominativeMonths($this->date->format('F Y'));
        }
        return 'rok ' . $this->date->format('Y');
    }

    private function nominativeMonths(string $dateFormat): string
    {
        return $this->translate($dateFormat, [
            'January'   => 'styczeń',
            'February'  => 'luty',
            'March'     => 'marzec',
            'April'     => 'kwiecień',
            'May'       => 'maj',
            'June'      => 'czerwiec',
            'July'      => 'lipiec',
            'August'    => 'sierpień',
            'September' => 'wrzesień',
            'October'   => 'październik',
            'November'  => 'listopad',
            'December'  => 'grudzień',
        ]);
    }

    private function genitiveMonths(string $dateFormat): string
    {
        return $this->translate($dateFormat, [
            'January'   => 'stycznia',
            'February'  => 'lutego',
            'March'     => 'marca',
            'April'     => 'kwietnia',
            'May'       => 'maja',
            'June'      => 'czerwca',
            'July'      => 'lipca',
            'August'    => 'sierpnia',
            'September' => 'września',
            'October'   => 'października',
            'November'  => 'listopada',
            'December'  => 'grudnia',
        ]);
    }

    private function translate(string $string, array $map): string
    {
        return \str_replace(\array_keys($map), \array_values($map), $string);
    }
}
