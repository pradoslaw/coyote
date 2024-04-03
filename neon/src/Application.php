<?php
namespace Neon;

use Neon\View\Language\Polish;
use Neon\View\View;

readonly class Application
{
    public function __construct(
        private string                 $applicationName,
        private Persistence\Attendance $attendance,
        private Persistence\JobOffers  $jobOffers,
        private Persistence\Events     $events,
        private Domain\Visitor         $visitor,
    )
    {
    }

    public function html(): string
    {
        $view = new View(
            new Polish(),
            $this->applicationName,
            $this->events->fetchEvents(),
            $this->attendance->fetchAttendance(),
            $this->jobOffers->fetchJobOffers(), // todo this is untested
            $this->visitor);
        return $view->html();
    }
}
