<?php
namespace Neon\Test\Unit\Event;

use Neon\View;
use PHPUnit\Framework\TestCase;

class SectionViewTest extends TestCase
{
    use Fixture\ViewFixture;

    /**
     * @test
     */
    public function manyEvents(): void
    {
        $view = new View(
            '',
            '', [
            new View\Event($this->viewEvent(['eventTitle' => 'Hear me roar'])),
            new View\Event($this->viewEvent(['eventTitle' => 'Ours is the fury'])),
        ]);
        $this->assertSame(
            ['Hear me roar', 'Ours is the fury'],
            $this->texts($view, '/html/body/div/h2'));
    }
}
