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
    public function detailsKindConference(): void
    {
        $view = $this->polishView(['eventKind' => EventKind::Conference]);
        $this->assertSame(
            'Konferencja',
            $this->eventDetailsKind($view));
    }

    /**
     * @test
     */
    public function detailsKindHackaton(): void
    {
        $view = $this->polishView(['eventKind' => EventKind::Hackaton]);
        $this->assertSame(
            'Hackaton',
            $this->eventDetailsKind($view));
    }

    /**
     * @test
     */
    public function detailsKindWorkshop(): void
    {
        $view = $this->polishView(['eventKind' => EventKind::Workshop]);
        $this->assertSame(
            'Warsztaty',
            $this->eventDetailsKind($view));
    }

    /**
     * @test
     */
    public function detailsPricing(): void
    {
        $view = $this->polishView(['eventFree' => false]);
        $this->assertSame(
            'Płatne',
            $this->eventDetailsPricing($view));
    }

    private function polishView(array $fields): \Neon\View\HtmlView
    {
        return $this->view($fields, new Polish());
    }
}
