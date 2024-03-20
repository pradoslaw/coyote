<?php
namespace Neon;

use Neon\View\Navigation;
use Neon\View\Section;

readonly class Application
{
    public function __construct(private string $applicationName)
    {
    }

    public function html(): string
    {
        $event = new \Neon\View\Event(
            new \Neon\ViewModel\Event(new \Neon\Domain\Event(
                'Global Day of Coderetreat 2023 (Software Crafers)',
                'Kraków',
                true,
                ['IT', 'BigData', 'Analityka', 'AI'],
                new \Neon\Domain\Date(2024, 12, 1),
                \Neon\Domain\EventKind::Conference,
            )));
        $view = new \Neon\View($this->applicationName, [
            new Navigation(new \Neon\ViewModel\Navigation(
                ['Forum', 'Microblogs', 'Jobs', 'Wiki'],
                '',
                'Coyote',
                '?',
                ['Register', 'Login'],
            )),
            new Section($this->applicationName,
                'Incoming events',
                [$event, $event]),
        ]);
        return $view->html();
    }
}
