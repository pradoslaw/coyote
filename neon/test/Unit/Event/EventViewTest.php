<?php
namespace Neon\Test\Unit\Event;

use Neon\Domain;
use Neon\Domain\EventKind;
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
            $this->text($view, '/html/body/div[@class="event"]/div/h2/text()'));
    }

    /**
     * @test
     */
    public function date(): void
    {
        $view = $this->view(['eventDate' => new Domain\Date(2024, 3, 18)]);
        $this->assertSame(
            ['03.18', 'Mon'],
            $this->texts($view, '/html/body/div[@class="event"]/div/span/text()'));
    }

    /**
     * @test
     */
    public function tags(): void
    {
        $view = $this->view(['eventTags' => ['rust', 'dart']]);
        $this->assertSame(
            ['rust', 'dart'],
            $this->texts($view, '/html/body/div[@class="event"]/div/ul/li/text()'));
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
            $this->texts($view, '/html/body/div[@class="event"]/div/div/text()'));
    }
}
