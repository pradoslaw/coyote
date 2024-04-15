<?php
namespace Xenon\Test\Unit;

use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\Assert;
use Xenon\Test\Fixture\ClientRuntime\Runtime;
use Xenon\Xenon;

trait Fixture
{
    function assertHtml(Xenon $xenon, string $expectedBody): void
    {
        $this->_assertHtmlBody($xenon->html(), $expectedBody);
    }

    function assertHtmlRuntime(Xenon $xenon, string $script, string $expectedBody): void
    {
        $this->_assertHtmlBody(
            $this->__runtimeHtml($xenon, $script),
            $expectedBody);
    }

    function _assertHtmlBody(string $html, string $expectedBody): void
    {
        $viewDom = new ViewDom($html);
        Assert::assertSame(
            $expectedBody,
            $viewDom->innerHtml('/html/body'));
    }

    function __runtimeHtml(Xenon $xenon, string $script): string
    {
        $runtime = new Runtime();
        $runtime->setHtmlSource($xenon->html());
        $runtime->executeScript($script);
        $result = $runtime->getDocumentHtml();
        $runtime->close();
        return $result;
    }
}
