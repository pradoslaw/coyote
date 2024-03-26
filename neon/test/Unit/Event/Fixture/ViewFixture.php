<?php
namespace Neon\Test\Unit\Event\Fixture;

use Neon;
use Neon\Domain;
use Neon\Domain\EventKind;
use Neon\Test\BaseFixture\Selector\Selector;
use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View;

trait ViewFixture
{
    function view(array $fields): Neon\View\HtmlView
    {
        return new Neon\View\HtmlView([], [
            new View\Html\Section(
                '',
                $fields['sectionTitle'] ?? '',
                [new View\Html\Event($this->viewEvent($fields))]),
        ]);
    }

    function text(Neon\View\HtmlView $view, Selector $selector): string
    {
        $dom = new ViewDom($view->html());
        return $dom->find($selector->xPath());
    }

    function texts(Neon\View\HtmlView $view, Selector $selector): array
    {
        $dom = new ViewDom($view->html());
        return $dom->findMany($selector->xPath());
    }

    function viewEvent(array $fields): Neon\View\ViewModel\Event
    {
        return new Neon\View\ViewModel\Event(new Domain\Event(
            $fields['eventTitle'] ?? '',
            $fields['eventCity'] ?? '',
            $fields['eventFree'] ?? true,
            $fields['eventTags'] ?? [],
            $fields['eventDate'] ?? new Domain\Date(0, 0, 0),
            $fields['eventKind'] ?? EventKind::Conference,
        ));
    }
}
