<?php
namespace Neon\Test\Unit\Event;

use Neon\Domain;
use Neon\Domain\Event\EventKind;
use Neon\Test\BaseFixture\ItemView;
use Neon\Test\Unit\Event;
use Neon\View\Html;
use PHPUnit\Framework\TestCase;

class EventViewTest extends TestCase
{
    use Event\Fixture\ViewFixture;

    /**
     * @test
     */
    public function title(): void
    {
        $view = $this->eventsSection(['eventTitle' => 'Ours is the Fury']);
        $this->assertSame(
            'Ours is the Fury',
            $view->find('div.event', 'h2'));
    }

    /**
     * @test
     */
    public function date(): void
    {
        $view = $this->eventsSection(['eventDate' => new Domain\Event\Date(2024, 3, 18)]);
        $this->assertSame(
            ['03.18', '|', 'Mon'],
            $view->findMany('div.event', 'div.date', 'span'));
    }

    /**
     * @test
     */
    public function tags(): void
    {
        $view = $this->eventsSection(['eventTags' => ['rust', 'dart']]);
        $this->assertSame(
            ['rust', 'dart'],
            $view->findMany('div.event', 'ul', 'li'));
    }

    /**
     * @test
     */
    public function details(): void
    {
        $view = $this->eventsSection([
            'eventCity' => 'Winterfell',
            'eventKind' => EventKind::Hackaton,
            'eventFree' => false,
        ]);
        $this->assertSame(
            ['Winterfell', 'Hackaton', 'Paid'],
            $this->eventDetails($view));
    }

    /**
     * @test
     */
    public function manyEvents(): void
    {
        $view = $this->eventSectionEvents(['Hear me roar', 'Ours is the fury']);
        $this->assertSame(
            ['Hear me roar', 'Ours is the fury'],
            $view->findMany('div.event', 'h2'));
    }
}
