<?php
namespace Neon;

use Neon\Domain\Date;
use Neon\Domain\Event;
use Neon\Domain\EventKind;
use Neon\View\Language\Polish;
use Neon\View\View;

class Application
{
    /** @var callable */
    private $attendance;

    public function __construct(readonly private string $applicationName, callable $attendance)
    {
        $this->attendance = $attendance;
    }

    public function html(): string
    {
        $view = new View(
            new Polish(),
            $this->applicationName,
            $this->events(),
            ($this->attendance)());
        return $view->html();
    }

    /**
     * @return Domain\Event[]
     */
    private function events(): array
    {
        return [
            new Event(
                '4DEVELOPERS',
                'Warszawa',
                false,
                ['Software', 'Hardware'],
                new Date(2024, 4, 16),
                EventKind::Conference,
            ),
            new Event(
                'Best Hacking League',
                'Warszawa',
                true,
                ['Software', 'Hardware', 'AI', 'Cybersecurity'],
                new Date(2024, 4, 20),
                EventKind::Hackaton,
            ),
            new Event(
                'Founders Mind VII',
                'Warszawa',
                false,
                ['Biznes', 'Networking'],
                new Date(2024, 5, 14),
                EventKind::Conference,
            ),
        ];
    }
}
