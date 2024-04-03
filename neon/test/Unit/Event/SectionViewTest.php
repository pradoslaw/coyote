<?php
namespace Neon\Test\Unit\Event;

use Neon\Test\BaseFixture\Selector\Selector;
use Neon\View;
use Neon\View\HtmlView;
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

    private function assertEventTitles(array $expected, HtmlView $view): void
    {
        $this->assertSame(
            $expected,
            $this->texts($view, new Selector('div.event', 'h2')));
    }

    private function viewWithEvents(array $titles): HtmlView
    {
        return new HtmlView([], [
            new View\Html\Body\Section('', '', '', '',
                \array_map(
                    fn(string $title) => new View\Html\Body\Event(
                        $this->viewEvent(['eventTitle' => $title])),
                    $titles)),
        ]);
    }
}
