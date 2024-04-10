<?php
namespace Neon\View\Components\Event;

use Neon\Domain;
use Neon\View\Language\Language;

readonly class Event
{
    public string $title;
    public string $titleUrl;
    public string $city;
    public array $tags;
    public string $pricing;
    public string $kind;
    public string $date;
    public string $dayShortName;

    public function __construct(Language $language, Domain\Event\Event $event)
    {
        $this->title = $event->title;
        $this->titleUrl = $event->url;
        $this->city = $event->city;
        $this->tags = $event->tags;
        $this->pricing = $language->t($event->free ? 'Free' : 'Paid');
        $this->kind = $language->t($event->kind->name);
        $this->date = $this->date($event);
        $this->dayShortName = $language->t($this->dayShortName($event->date));
    }

    private function date(Domain\Event\Event $event): string
    {
        $day = $this->leadingZero($event->date->day);
        $month = $this->leadingZero($event->date->month);
        return "$day.$month.{$event->date->year}";
    }

    private function leadingZero(string $string): string
    {
        if (\strLen($string) === 1) {
            return '0' . $string;
        }
        return $string;
    }

    private function dayShortName(Domain\Event\Date $date): string
    {
        return date('D', \mkTime(
            hour:0,
            day:$date->day,
            month:$date->month,
            year:$date->year));
    }
}
