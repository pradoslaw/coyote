<?php
namespace Neon\Test\Unit\Event\Fixture;

use Neon;
use Neon\Domain;
use Neon\Domain\Event\EventKind;
use Neon\Test\BaseFixture\Selector\Selector;
use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View;
use Neon\View\HtmlView;
use Neon\View\Language\English;
use Neon\View\Language\Language;

trait ViewFixture
{
    function eventDetails(HtmlView $view): array
    {
        return $this->texts($view, new Selector('div.event', 'div.details', 'span'));
    }

    function eventDetailsPricing(HtmlView $view): string
    {
        return $this->text($view, new Selector('div.event', 'div.details', 'span[last()]'));
    }

    function eventDayShortName(HtmlView $view): string
    {
        return $this->text($view, new Selector('div.event', 'div.date', 'span[last()]'));
    }

    function eventDetailsKind(HtmlView $view): string
    {
        return $this->text($view, new Selector('div.event', 'div.details', 'span[2]'));
    }

    function view(array $fields, Language $lang = null): HtmlView
    {
        return new HtmlView([], [
            new View\Html\Body\Section(
                '',
                '',
                $fields['sectionTitle'] ?? '',
                '',
                [new View\Html\Body\Event($this->viewEvent($fields, $lang))]),
        ]);
    }

    function text(HtmlView $view, Selector $selector): string
    {
        $dom = new ViewDom($view->html());
        return $dom->find($selector->xPath());
    }

    function texts(HtmlView $view, Selector $selector): array
    {
        $dom = new ViewDom($view->html());
        return $dom->findMany($selector->xPath());
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
