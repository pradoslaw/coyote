<?php
namespace Neon;

use Neon\Domain\Event\Date;
use Neon\Domain\Event\Event;
use Neon\Domain\Event\EventKind;
use Neon\Domain\Visitor;
use Neon\View\Language\Polish;
use Neon\View\View;

readonly class Application
{
    public function __construct(
        private string                 $applicationName,
        private Persistence\Attendance $attendance,
        private Persistence\JobOffers  $jobOffers,
        private Visitor                $visitor,
    )
    {
    }

    public function html(): string
    {
        $view = new View(
            new Polish(),
            $this->applicationName,
            $this->events(),
            $this->attendance->fetchAttendance(),
            $this->jobOffers->fetchJobOffers(), // todo this is untested
            $this->visitor);
        return $view->html();
    }

    /**
     * @return \Neon\Domain\Event\Event[]
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
