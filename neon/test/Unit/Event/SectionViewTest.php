<?php
namespace Neon\Test\Unit\Event;

use Neon\Test\BaseFixture\Selector\Selector;
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
        $view = $this->viewWithEvents(['Hear me roar', 'Ours is the fury']);
        $this->assertEventTitles(['Hear me roar', 'Ours is the fury'], $view);
    }

    private function assertEventTitles(array $expected, View $view): void
    {
        $this->assertSame(
            $expected,
            $this->texts($view, new Selector('html', 'body', 'div', 'div.event', 'div', 'h2')));
    }

    private function viewWithEvents(array $titles): View
    {
        return new View('', [
            new View\Section('', '',
                \array_map(
                    fn(string $title) => new View\Event(
                        $this->viewEvent(['eventTitle' => $title])),
                    $titles)),
        ]);
    }
}
