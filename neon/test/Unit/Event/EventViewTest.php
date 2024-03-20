<?php
namespace Neon\Test\Unit\Event;

use Neon\Domain;
use Neon\Domain\EventKind;
use Neon\Test\BaseFixture\Selector\Selector;
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
        $view = $this->view(['eventTitle' => 'Ours is the Fury']);
        $this->assertSame(
            'Ours is the Fury',
            $this->text($view, new Selector('html', 'body', 'div.event', 'div', 'h2')));
    }

    /**
     * @test
     */
    public function date(): void
    {
        $view = $this->view(['eventDate' => new Domain\Date(2024, 3, 18)]);
        $this->assertSame(
            ['03.18', 'Mon'],
            $this->texts($view, new Selector('html', 'body', 'div.event', 'div', 'span')));
    }

    /**
     * @test
     */
    public function tags(): void
    {
        $view = $this->view(['eventTags' => ['rust', 'dart']]);
        $this->assertSame(
            ['rust', 'dart'],
            $this->texts($view, new Selector('html', 'body', 'div.event', 'div', 'ul', 'li')));
    }

    /**
     * @test
     */
    public function details(): void
    {
        $view = $this->view([
            'eventCity' => 'Winterfell',
            'eventKind' => EventKind::Hackaton,
            'eventFree' => false,
        ]);
        $this->assertSame(
            ['Winterfell', 'Hackaton', 'Paid'],
            $this->texts($view, new Selector('html', 'body', 'div.event', 'div', 'div')));
    }
}
