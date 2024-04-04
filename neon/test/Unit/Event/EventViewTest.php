<?php
namespace Neon\Test\Unit\Event;

use Neon\Domain;
use Neon\Test\Unit\Event;
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
            $view->find('div.event', 'h2', 'a'));
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
    public function titleLink(): void
    {
        $view = $this->eventsSection([
            'eventLink' => 'link.com/',
        ]);
        $this->assertSame(
            'link.com/',
            $view->find('div.event', 'h2', 'a', '@href'));
    }

    /**
     * @test
     */
    public function manyEvents(): void
    {
        $view = $this->eventSectionEvents(['Hear me roar', 'Ours is the fury']);
        $this->assertSame(
            ['Hear me roar', 'Ours is the fury'],
            $view->findMany('div.event', 'h2', 'a'));
    }
}
