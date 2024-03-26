<?php
namespace Neon;

use Neon\View\Attendance;
use Neon\View\Head\Favicon;
use Neon\View\Head\Title;
use Neon\View\Item;
use Neon\View\Navigation;
use Neon\View\Section;

readonly class Application
{
    public function __construct(private string $applicationName)
    {
    }

    public function html(): string
    {
        $view = new \Neon\HtmlView([
            new Title($this->applicationName),
            new Favicon('https://4programmers.net/img/favicon.png'),
        ],
            [
                new Navigation(new \Neon\ViewModel\Navigation(
                    [
                        'Forum'      => '/Forum',
                        'Microblogs' => '/Mikroblogi',
                        'Jobs'       => '/Praca',
                        'Wiki'       => '/Kategorie',
                    ],
                    'https://github.com/pradoslaw/coyote',
                    'https://github.com/pradoslaw/coyote/stargazers',
                    'Coyote',
                    '111',
                    [
                        'Create account' => '/Register',
                        'Login'          => '/Login',
                    ],
                )),
                $this->asideMain(
                    new Attendance(
                        '116.408', '124',
                        'Users', 'Online'),
                    new Section(
                        $this->applicationName,
                        'Incoming events',
                        $this->events())),
            ]);
        return $view->html();
    }

    private function asideMain(Item $aside, Item $main): Item
    {
        return new UntypedItem(fn(callable $h): array => [
            $h('div', [
                $h('aside', $aside->html($h), 'lg:w-1/4 lg:pr-2 mb-4 lg:mb-0'),
                $h('main', $main->html($h), 'lg:w-3/4 lg:pl-2'),
            ], 'lg:flex container mx-auto'),
        ]);
    }

    private function events(): array
    {
        $sForce = new \Neon\View\Event(
            new \Neon\ViewModel\Event(new \Neon\Domain\Event(
                'SForce Day 2024',
                'Warszawa',
                true,
                ['Salesforce'],
                new \Neon\Domain\Date(2024, 3, 26),
                \Neon\Domain\EventKind::Conference,
            )));
        $azureSummit = new \Neon\View\Event(
            new \Neon\ViewModel\Event(new \Neon\Domain\Event(
                'Azure Summit 2024',
                'Online',
                true,
                ['Azure', 'Microsoft'],
                new \Neon\Domain\Date(2024, 3, 28),
                \Neon\Domain\EventKind::Conference,
            )));
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

        return [
            $sForce,
            $azureSummit,
            $_4developers,
            $hackingLeague,
            $foundersMind,
        ];
    }
}
