<?php
namespace Neon\Test\Unit\Event;

use Neon\Domain;
use Neon\Domain\Date;
use PHPUnit\Framework\TestCase;

class ViewModelTest extends TestCase
{
    /**
     * @test
     */
    public function identityTitle(): void
    {
        $event = $this->viewEvent(['title' => 'Hear me roar!']);
        $this->assertSame('Hear me roar!', $event->title);
    }

    /**
     * @test
     */
    public function identityCity(): void
    {
        $event = $this->viewEvent(['city' => 'Lannisport']);
        $this->assertSame('Lannisport', $event->city);
    }

    /**
     * @test
     */
    public function pricingFree(): void
    {
        $event = $this->viewEvent(['free' => true]);
        $this->assertSame('Free', $event->pricing);
    }

    /**
     * @test
     */
    public function pricingPaid(): void
    {
        $event = $this->viewEvent(['free' => false]);
        $this->assertSame('Paid', $event->pricing);
    }

    /**
     * @test
     */
    public function identityTags(): void
    {
        $event = $this->viewEvent(['tags' => ['gold', 'crown']]);
        $this->assertSame(['gold', 'crown'], $event->tags);
    }

    /**
     * @test
     */
    public function kindWorkshop(): void
    {
        $event = $this->viewEvent(['kind' => Domain\EventKind::Workshop]);
        $this->assertSame('Workshop', $event->kind);
    }

    /**
     * @test
     */
    public function kindConference(): void
    {
        $event = $this->viewEvent(['kind' => Domain\EventKind::Conference]);
        $this->assertSame('Conference', $event->kind);
    }

    /**
     * @test
     */
    public function kindHackaton(): void
    {
        $event = $this->viewEvent(['kind' => Domain\EventKind::Hackaton]);
        $this->assertSame('Hackaton', $event->kind);
    }

    /**
     * @test
     */
    public function date(): void
    {
        $event = $this->viewEvent(['date' => new Date(2023, 11, 15)]);
        $this->assertSame('11.15', $event->date);
    }

    /**
     * @test
     */
    public function dateLeadingZero(): void
    {
        $event = $this->viewEvent(['date' => new Date(2023, 2, 1)]);
        $this->assertSame('02.01', $event->date);
    }

    /**
     * @test
     */
    public function dateDayNameFriday(): void
    {
        $this->assertDayShortName(new Date(2000, 1, 2), 'Sun');
    }

    /**
     * @test
     */
    public function dateDayNameSaturday(): void
    {
        $this->assertDayShortName(new Date(2000, 1, 3), 'Mon');
    }

    private function assertDayShortName(Date $date, string $expected): void
    {
        $event = $this->viewEvent(['date' => $date]);
        $this->assertSame($expected, $event->dayShortName);
    }

    private function viewEvent(array $fields): \Neon\ViewModel\Event
    {
        return new \Neon\ViewModel\Event($this->domainEvent($fields));
    }

    private function domainEvent(array $fields): Domain\Event
    {
        return new Domain\Event(
            $fields['title'] ?? 'irrelevant',
            $fields['city'] ?? 'irrelevant',
            $fields['free'] ?? false,
            $fields['tags'] ?? [],
            $fields['date'] ?? new Date(130, 2, 4),
            $fields['kind'] ?? Domain\EventKind::Conference,
        );
    }
}
