<?php
namespace Neon;

use Neon\Domain\Attendance;
use Neon\Domain\Date;
use Neon\Domain\Event;
use Neon\Domain\EventKind;
use Neon\View\View;

readonly class Application
{
    private View $view;

    public function __construct(string $applicationName, Attendance $attendance)
    {
        $this->view = new View($applicationName, $this->events(), $attendance);
    }

    public function html(): string
    {
        return $this->view->html();
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
