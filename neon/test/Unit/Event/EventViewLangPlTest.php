<?php
namespace Neon\Test\Unit\Event;

use Neon\Domain\Event\Date;
use Neon\Domain\Event\EventKind;
use Neon\Test\Unit\Event;
use Neon\View\Language\Polish;
use PHPUnit\Framework\TestCase;

class EventViewLangPlTest extends TestCase
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

    /**
     * @test
     * @dataProvider shortNames
     */
    public function detailsDayShortName(int $index, string $expectedName): void
    {
        $this->assertSame($expectedName, $this->dayShortName($index));
    }

    public function shortNames(): array
    {
        return [
            [0, 'Pn'],
            [1, 'Wt'],
            [2, 'Śr'],
            [3, 'Cz'],
            [4, 'Pt'],
            [5, 'Sb'],
            [6, 'Nd'],
        ];
    }

    private function dayShortName(int $i): string
    {
        $view = $this->polishView(['eventDate' => new Date(2024, 1, $i + 1)]);
        return $this->eventDayShortName($view);
    }

    private function polishView(array $fields): \Neon\View\HtmlView
    {
        return $this->view($fields, new Polish());
    }
}
