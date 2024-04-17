<?php
namespace Xenon\Test\Fixture\ClientRuntime;

use Tests\Legacy\TestCase;
use function Neon\Test\BaseFixture\Caught\caught;

class RuntimeTest extends TestCase
{
    private Runtime $runtime;

    /**
     * @before
     */
    public function createDriver(): void
    {
        $this->runtime = new Runtime();
    }

    /**
     * @after
     */
    public function closeDriver(): void
    {
        $this->runtime->close();
    }

    /**
     * @test
     */
    public function identity(): void
    {
        $this->runtime->setHtmlSource('Foo');
        $this->assertSame(
            '<head></head><body>Foo</body>',
            $this->runtime->getDocumentHtml());
    }

    /**
     * @test
     */
    public function javascriptError(): void
    {
        $exception = caught(fn() => $this->runtime->setHtmlSource('<script>throw new Error("foo");</script>'));
        $this->assertSame(
            'Uncaught Error: foo',
            $exception->getMessage());
    }

    /**
     * @test
     */
    public function javascriptSyntaxError(): void
    {
        $exception = caught(fn() => $this->runtime->setHtmlSource('<script>return;</script>'));
        $this->assertSame(
            'Uncaught SyntaxError: Illegal return statement',
            $exception->getMessage());
    }

    /**
     * @test
     */
    public function javascriptConsoleLogHtml(): void
    {
        $this->runtime->setHtmlSource("<script>console.log('foo', 4, false);</script>");
        $this->assertSame(['"foo" 4 false'], $this->runtime->consoleLogs());
    }

    /**
     * @test
     */
    public function javascriptConsoleLogScript(): void
    {
        $this->runtime->executeScript("console.log('bar', 15, true);");
        $this->assertSame(['"bar" 15 true'], $this->runtime->consoleLogs());
    }

    /**
     * @test
     */
    public function executeScript(): void
    {
        $this->runtime->executeScript("window.document.body.innerHTML='runtime';");
        $this->assertSame(
            '<head></head><body>runtime</body>',
            $this->runtime->getDocumentHtml());
    }

    /**
     * @test
     */
    public function scriptException(): void
    {
        $exception = caught(fn() => $this->runtime->executeScript('missing;'));
        $this->assertSame(
            'missing is not defined',
            $exception->getMessage());
    }

    /**
     * @test
     */
    public function htmlPoundSign(): void
    {
        $this->runtime->setHtmlSource("'#foo';");
        $this->assertSame(
            "<head></head><body>'#foo';</body>",
            $this->runtime->getDocumentHtml());
    }

    /**
     * @test
     */
    public function clearPendingLogs(): void
    {
        $this->runtimeWithPendingLogs();
        $this->runtime->close();
        $this->assertSame([], $this->runtime->consoleLogs());
    }

    /**
     * @test
     */
    public function clearMaterializedLogs(): void
    {
        $this->runtimeWithMaterializedLogs();
        $this->runtime->close();
        $this->assertSame([], $this->runtime->consoleLogs());
    }

    /**
     * @test
     */
    public function clearConsoleLogs(): void
    {
        $this->runtime->executeScript("console.log('log');");
        $this->runtime->clearConsoleLogs();
        $this->assertSame([], $this->runtime->consoleLogs());
    }

    /**
     * @test
     */
    public function click(): void
    {
        $this->runtime->setHtmlSource(<<<'html'
            <button onClick="console.log('clicked')">
                Click me
            </button>
            html,);
        $this->runtime->click('//button');
        $this->assertSame(
            ['"clicked"'],
            $this->runtime->consoleLogs());
    }

    private function runtimeWithPendingLogs(): void
    {
        $this->runtime->executeScript("console.log('foo');");
    }

    private function runtimeWithMaterializedLogs(): void
    {
        $this->runtime->executeScript("console.log('foo');");
        $this->runtime->consoleLogs();
    }
}
