<?php
namespace Neon\Test\Unit\Event\Fixture;

use Neon\Domain;
use Neon\Domain\Event\EventKind;
use Neon\Test\BaseFixture\ItemView;
use Neon\View;
use Neon\View\Components\SectionHtml;
use Neon\View\Language\English;
use Neon\View\Language\Language;

trait ViewFixture
{
    function eventDetails(ItemView $view): array
    {
        return $view->findMany('div.event', 'div.details', 'span');
    }

    function eventDetailsPricing(ItemView $view): string
    {
        return $view->find('div.event', 'div.details', 'span[last()]');
    }

    function eventDayShortName(ItemView $view): string
    {
        return $view->find('div.event', 'div.date', 'span[last()]');
    }

    function eventDetailsKind(ItemView $view): string
    {
        return $view->find('div.event', 'div.details', 'span[2]');
    }

    function eventsSection(array $fields, Language $lang = null): ItemView
    {
        return new ItemView(new SectionHtml(
            '',
            '',
            $fields['sectionTitle'] ?? '',
            '',
            [new View\Components\Event\EventHtml($this->viewEvent($fields, $lang))]));
    }

    function eventSectionEvents(array $titles): ItemView
    {
        return new ItemView(new View\Components\SectionHtml(
            '',
            '',
            '',
            '',
            \array_map(fn(string $title) => new View\Components\Event\EventHtml(
                $this->viewEvent(['eventTitle' => $title])),
                $titles)));
    }

    function viewEvent(array $fields, Language $lang = null): View\Components\Event\Event
    {
        return new View\Components\Event\Event(
            $lang ?? new English(),
            new Domain\Event\Event(
                $fields['eventTitle'] ?? '',
                $fields['eventCity'] ?? '',
                $fields['eventFree'] ?? true,
                $fields['eventTags'] ?? [],
                $fields['eventDate'] ?? new Domain\Event\Date(0, 0, 0),
                $fields['eventKind'] ?? EventKind::Conference,
                $fields['eventLink'] ?? '',
                '',
            ));
    }
}
