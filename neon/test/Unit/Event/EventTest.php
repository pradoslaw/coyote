<?php
namespace Neon\Test\Unit\Event;

use Neon\Domain\Event\Date;
use Neon\Domain\Event\Event;
use Neon\Domain\Event\EventKind;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    /**
     * @test
     */
    public function title(): void
    {
        $event = $this->event(['title' => 'Winter is coming!']);
        $this->assertSame('Winter is coming!', $event->title);
    }

    /**
     * @test
     */
    public function freeEvent(): void
    {
        $event = $this->event(['free' => true]);
        $this->assertTrue($event->free);
    }

    /**
     * @test
     */
    public function paidEvent(): void
    {
        $event = $this->event(['free' => false]);
        $this->assertFalse($event->free);
    }

    /**
     * @test
     */
    public function city(): void
    {
        $event = $this->event(['city' => 'Hong Kong']);
        $this->assertSame('Hong Kong', $event->city);
    }

    /**
     * @test
     */
    public function tags(): void
    {
        $event = $this->event(['tags' => ['java', 'kotlin']]);
        $this->assertSame(['java', 'kotlin'], $event->tags);
    }

    /**
     * @test
     */
    public function date(): void
    {
        $event = $this->event(['date' => new Date(2023, 02, 03)]);
        $this->assertEquals(2023, $event->date->year);
        $this->assertEquals(2, $event->date->month);
        $this->assertEquals(3, $event->date->day);
    }

    /**
     * @test
     */
    public function kindHackaton(): void
    {
        $this->assertEventKind(EventKind::Hackaton);
    }

    /**
     * @test
     */
    public function kindWorkshop(): void
    {
        $this->assertEventKind(EventKind::Conference);
    }

    /**
     * @test
     */
    public function kindConference(): void
    {
        $this->assertEventKind(EventKind::Workshop);
    }

    private function assertEventKind(EventKind $kind): void
    {
        $event = $this->event(['kind' => $kind]);
        $this->assertEquals($kind, $event->kind);
    }

    private function event(array $fields): Event
    {
        return new Event(
            $fields['title'] ?? 'irrelevant',
            $fields['city'] ?? 'irrelevant',
            $fields['free'] ?? false,
            $fields['tags'] ?? [],
            $fields['date'] ?? new Date(0, 0, 0),
            $fields['kind'] ?? EventKind::Workshop,
        );
    }
}
