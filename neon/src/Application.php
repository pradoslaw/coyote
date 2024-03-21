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
        $_4developers = new \Neon\View\Event(
            new \Neon\ViewModel\Event(new \Neon\Domain\Event(
                '4DEVELOPERS',
                'Warszawa',
                false,
                ['Software', 'Hardware'],
                new \Neon\Domain\Date(2024, 4, 16),
                \Neon\Domain\EventKind::Conference,
            )));
        $foundersMind = new \Neon\View\Event(
            new \Neon\ViewModel\Event(new \Neon\Domain\Event(
                'Founders Mind VII',
                'Warszawa',
                false,
                ['Biznes', 'Networking'],
                new \Neon\Domain\Date(2024, 5, 14),
                \Neon\Domain\EventKind::Conference,
            )));
        $hackingLeague = new \Neon\View\Event(
            new \Neon\ViewModel\Event(new \Neon\Domain\Event(
                'Best Hacking League',
                'Warszawa',
                true,
                ['Software', 'Hardware', 'AI', 'Cybersecurity'],
                new \Neon\Domain\Date(2024, 4, 20),
                \Neon\Domain\EventKind::Hackaton,
            )));

        $events = [
            $_4developers,
            $hackingLeague,
            $foundersMind,
        ];

        $view = new \Neon\View($this->applicationName, [
            new Navigation(new \Neon\ViewModel\Navigation(
                ['Forum', 'Microblogs', 'Jobs', 'Wiki'],
                '',
                'Coyote',
                '14',
                ['Create account', 'Login'],
            )),
            new Section($this->applicationName, 'Incoming events', $events),
        ]);
        return $view->html();
    }
}
