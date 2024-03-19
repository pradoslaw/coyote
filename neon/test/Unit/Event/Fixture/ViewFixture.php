<?php
namespace Neon\Test\Unit\Event\Fixture;

use Neon;
use Neon\Domain;
use Neon\Domain\EventKind;
use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View;
use Neon\ViewModel;

trait ViewFixture
{
    function view(array $fields): Neon\View
    {
        return new Neon\View(
            $fields['applicationName'] ?? '',
            $fields['sectionTitle'] ?? '',
            [new View\Event($this->viewEvent($fields))],
        );
    }

    function text(Neon\View $view, string $xPath): string
    {
        $dom = new ViewDom($view->html());
        return $dom->find($xPath);
    }

    function texts(Neon\View $view, string $xPath): array
    {
        $dom = new ViewDom($view->html());
        return $dom->findMany($xPath);
    }

    function viewEvent(array $fields): ViewModel\Event
    {
        return new ViewModel\Event(new Domain\Event(
            $fields['eventTitle'] ?? '',
            $fields['eventCity'] ?? '',
            $fields['eventFree'] ?? true,
            $fields['eventTags'] ?? [],
            $fields['eventDate'] ?? new Domain\Date(0, 0, 0),
            $fields['eventKind'] ?? EventKind::Conference,
        ));
    }
}
