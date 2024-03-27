<?php
namespace Neon\View;

use Neon\Domain;
use Neon\Domain\Attendance;
use Neon\View\Html\Head\Favicon;
use Neon\View\Html\Head\Title;
use Neon\View\Html\Item;
use Neon\View\Html\Navigation;
use Neon\View\Html\UntypedItem;
use Neon\View\Language\Language;
use Neon\View\Language\Polish;

readonly class View
{
    private Language $lang;
    private HtmlView $view;

    public function __construct(string $applicationName, array $events, Attendance $attendance)
    {
        $this->lang = new Polish();
        $this->view = new HtmlView([
            new Title($applicationName),
            new Favicon('https://4programmers.net/img/favicon.png'),
        ], [
            new Navigation($this->navigation()),
            $this->asideMain(
                $this->attendance($attendance),
                $this->eventsSection($applicationName, $events)),
        ]);
    }

    public function html(): string
    {
        return $this->view->html();
    }

    private function navigation(): ViewModel\Navigation
    {
        return new ViewModel\Navigation(
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
        );
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

    private function attendance(Attendance $attendance): Html\Attendance
    {
        $vm = new ViewModel\Attendance($attendance);
        return new Html\Attendance(
            $vm->totalUsers, $vm->onlineUsers,
            'Users', 'Online');
    }

    private function eventsSection(string $applicationName, array $events): Html\Section
    {
        return new Html\Section(
            $applicationName,
            'Incoming events',
            \array_map(
                fn(Domain\Event $event) => new Html\Event(
                    new ViewModel\Event($this->lang, $event)),
                $events,
            ));
    }
}
