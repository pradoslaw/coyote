<?php
namespace Neon\Test\Unit\Event;

use Neon\Domain\EventKind;
use Neon\Test\Unit\Event;
use Neon\View\Language\Polish;
use PHPUnit\Framework\TestCase;

class EventViewLangTest extends TestCase
{
    use Event\Fixture\ViewFixture;

    /**
     * @test
     */
    public function details(): void
    {
        $view = $this->view([
            'eventCity' => 'Winterfell',
            'eventKind' => EventKind::Hackaton,
            'eventFree' => false,
        ], new Polish());
        $this->assertSame(
            ['Winterfell', 'Hackaton', 'Płatne'],
            $this->eventDetails($view));
    }
}
