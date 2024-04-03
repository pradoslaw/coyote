<?php
namespace Neon\Test\Unit\Event\Fixture;

use Neon;
use Neon\Domain;
use Neon\Domain\Event\EventKind;
use Neon\Test\BaseFixture\ItemView;
use Neon\View;
use Neon\View\Html\Body\Section;
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
        return new ItemView(new Section(
            '',
            '',
            $fields['sectionTitle'] ?? '',
            '',
            [new View\Html\Body\Event($this->viewEvent($fields, $lang))]));
    }

    function viewEvent(array $fields, Language $lang = null): Neon\View\ViewModel\Event
    {
        return new Neon\View\ViewModel\Event(
            $lang ?? new English(),
            new Domain\Event\Event(
                $fields['eventTitle'] ?? '',
                $fields['eventCity'] ?? '',
                $fields['eventFree'] ?? true,
                $fields['eventTags'] ?? [],
                $fields['eventDate'] ?? new Domain\Event\Date(0, 0, 0),
                $fields['eventKind'] ?? EventKind::Conference,
            ));
    }
}
