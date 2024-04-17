<?php
namespace Xenon\Test\Unit\Event;

use PHPUnit\Framework\TestCase;
use Xenon\Tag;
use Xenon\Test\Fixture\ClientRuntime\Runtime;
use Xenon\Xenon;

class EventTest extends TestCase
{
    /**
     * @test
     */
    public function test(): void
    {
        $xenon = new Xenon([
            new Tag('button', [], [
                'focus' => "console.log('focused');",
                'click' => "console.log('clicked');",
            ], []),
        ], []);

        $this->assertSame(
            ['"focused"', '"clicked"'],
            $this->clickAndGetConsoleLogs($xenon, '//button'));
    }

    private function clickAndGetConsoleLogs(Xenon $xenon, string $xPath): array
    {
        $runtime = new Runtime();
        $runtime->setHtmlSource("<html><body>{$xenon->html()}</body></html>");
        $runtime->clearConsoleLogs();
        $runtime->click($xPath);
        $result = $runtime->consoleLogs();
        $runtime->close();
        return $result;
    }
}
