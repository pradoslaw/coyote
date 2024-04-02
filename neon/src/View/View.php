<?php
namespace Neon\View;

use Neon\Domain;
use Neon\Domain\Attendance;
use Neon\Domain\Visitor;
use Neon\View\Html\Head\Favicon;
use Neon\View\Html\Head\Title;
use Neon\View\Html\Item;
use Neon\View\Html\Navigation;
use Neon\View\Html\UntypedItem;
use Neon\View\Language\Language;

readonly class View
{
    private HtmlView $view;

    public function __construct(
        private Language $lang,
        string           $applicationName,
        array            $events,
        Attendance       $attendance,
        Visitor          $visitor)
    {
        $this->view = new HtmlView([
            new Title($applicationName),
            new Favicon('https://4programmers.net/img/favicon.png'),
        ], [
            new Navigation($this->navigation($visitor)),
            $this->asideMain(
                $this->attendance($attendance),
                $this->eventsSection($applicationName, $events)),
        ]);
    }

    public function html(): string
    {
        return $this->view->html();
    }

    private function navigation(Visitor $visitor): ViewModel\Navigation
    {
        return new ViewModel\Navigation(
            '/',
            [
                $this->lang->t('Forum')      => '/Forum',
                $this->lang->t('Microblogs') => '/Mikroblogi',
                $this->lang->t('Jobs')       => '/Praca',
                $this->lang->t('Wiki')       => '/Kategorie',
            ],
            'https://github.com/pradoslaw/coyote',
            'https://github.com/pradoslaw/coyote/stargazers',
            'Coyote',
            '111',
            [
                $this->lang->t('Create account') => '/Register',
                $this->lang->t('Login')          => '/Login',
            ],
            $visitor,
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
            $this->lang->t('Users'), 'Online');
    }

    private function eventsSection(string $applicationName, array $events): Html\Section
    {
        return new Html\Section(
            $applicationName,
            $this->lang->t('Events'),
            $this->lang->t('Incoming events'),
            $this->lang->t('Events with our patronage'),
            \array_map(
                fn(Domain\Event $event) => new Html\Event(
                    new ViewModel\Event($this->lang, $event)),
                $events,
            ));
    }
}
