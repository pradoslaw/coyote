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

    function assertHtmlRuntime(Xenon $xenon, string $expectedBody): void
    {
        $this->_assertHtmlBody($this->__runtimeHtml($xenon, null), $expectedBody);
    }

    function executeAndAssertHtmlRuntime(Xenon $xenon, string $script, string $expectedBody): void
    {
        $this->_assertHtmlBody(
            $this->__runtimeHtml($xenon, $script),
            $expectedBody);
    }

    function _assertHtmlBody(string $html, string $expectedBody): void
    {
        $viewDom = new ViewDom("<html><body>$html</body></html>");
        Assert::assertSame(
            $expectedBody,
            \implode('',
                $viewDom->collectionHtml(
                    "/html/body/*[not(name()='script')] | /html/body/text()")));
    }

    function __runtimeHtml(Xenon $xenon, ?string $script): string
    {
        $runtime = new Runtime();
        $runtime->setHtmlSource("<html><body>{$xenon->html()}</body></html>");
        if ($script) {
            $runtime->executeScript($script);
        }
        $result = $runtime->getDocumentHtml();
        $runtime->close();
        return $result;
    }
}
