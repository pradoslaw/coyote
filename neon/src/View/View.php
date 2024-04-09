<?php
namespace Neon\View;

use Neon\Domain;
use Neon\Domain\Attendance;
use Neon\Domain\Visitor;
use Neon\View\Html\Head\Favicon;
use Neon\View\Html\Head\Title;
use Neon\View\Html\Item;
use Neon\View\Html\Render;
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
        array            $offers,
        Visitor          $visitor)
    {
        $this->view = new HtmlView([
            new Title($applicationName),
            new Favicon('https://4programmers.net/img/favicon.png'),
        ], [
            new Components\Navigation\NavigationHtml($this->navigation($visitor)),
            new UntypedItem(fn(Render $h): array => [
                $h->tag('div', ['class' => 'lg:flex container mx-auto'], [
                    $h->tag('aside', ['class' => 'lg:w-1/4 lg:pr-2 mb-4 lg:mb-0'], [
                        ...$this->attendance($attendance)->render($h),
                        ...$this->jobOffers($offers)->render($h),
                    ]),
                    $h->tag('main',
                        ['class' => 'lg:w-3/4 lg:pl-2'],
                        $this->eventsSection($applicationName, $events)->render($h)),
                ]),
            ]),
        ]);
    }

    public function html(): string
    {
        return $this->view->html();
    }

    private function navigation(Visitor $visitor): Components\Navigation\Navigation
    {
        return new Components\Navigation\Navigation(
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

    private function attendance(Attendance $attendance): Components\Attendance\AttendanceHtml
    {
        return new Components\Attendance\AttendanceHtml(new Components\Attendance\Attendance($this->lang, $attendance));
    }

    private function eventsSection(string $applicationName, array $events): Components\SectionHtml
    {
        return new Components\SectionHtml(
            $applicationName,
            $this->lang->t('Events'),
            $this->lang->t('Incoming events'),
            $this->lang->t('Events with our patronage'),
            \array_map(
                fn(Domain\Event\Event $event) => new Components\Event\EventHtml(
                    new Components\Event\Event($this->lang, $event)),
                $events,
            ));
    }

    private function jobOffers(array $offers): Item
    {
        return new Components\JobOffer\JobOffersHtml($this->lang->t('Search for jobs'),
            \array_map(fn(Domain\JobOffer $offer) => new Components\JobOffer\JobOffer($this->lang, $offer),
                $offers));
    }
}
