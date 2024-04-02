<?php
namespace Neon\Test\Unit\Event;

use Neon\Domain\Event\Date;
use Neon\Domain\Event\EventKind;
use Neon\Test\Unit\Event;
use Neon\View\Language\English;
use PHPUnit\Framework\TestCase;

class EventViewLangEngTest extends TestCase
{
    use Event\Fixture\ViewFixture;

    /**
     * @test
     */
    public function detailsKindConference(): void
    {
        $view = $this->englishView(['eventKind' => EventKind::Conference]);
        $this->assertSame(
            'Conference',
            $this->eventDetailsKind($view));
    }

    /**
     * @test
     */
    public function detailsKindHackaton(): void
    {
        $view = $this->englishView(['eventKind' => EventKind::Hackaton]);
        $this->assertSame(
            'Hackaton',
            $this->eventDetailsKind($view));
    }

    /**
     * @test
     */
    public function detailsKindWorkshop(): void
    {
        $view = $this->englishView(['eventKind' => EventKind::Workshop]);
        $this->assertSame(
            'Workshop',
            $this->eventDetailsKind($view));
    }

    /**
     * @test
     */
    public function detailsPricing(): void
    {
        $view = $this->englishView(['eventFree' => false]);
        $this->assertSame(
            'Paid',
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
            [0, 'Mon'],
            [1, 'Tue'],
            [2, 'Wed'],
            [3, 'Thu'],
            [4, 'Fri'],
            [5, 'Sat'],
            [6, 'Sun'],
        ];
    }

    private function dayShortName(int $i): string
    {
        $view = $this->englishView(['eventDate' => new Date(2024, 1, $i + 1)]);
        return $this->eventDayShortName($view);
    }

    private function englishView(array $fields): \Neon\View\HtmlView
    {
        return $this->view($fields, new English());
    }
}
