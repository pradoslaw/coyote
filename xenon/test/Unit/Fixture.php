<?php
namespace Xenon\Test\Unit;

use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\Assert;
use Xenon\Xenon;

trait Fixture
{
    function assertHtml(Xenon $xenon, string $expectedBody): void
    {
        $this->_assertHtmlBody($xenon->html(), $expectedBody);
    }

    function _assertHtmlBody(string $html, string $expectedBody): void
    {
        $viewDom = new ViewDom($html);
        Assert::assertSame(
            $expectedBody,
            $viewDom->html('/html/body'));
    }
}
