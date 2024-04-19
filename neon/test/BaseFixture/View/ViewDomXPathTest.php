<?php
namespace Neon\Test\BaseFixture\View;

use PHPUnit\Framework\TestCase;
use function Neon\Test\BaseFixture\Caught\caught;

class ViewDomXPathTest extends TestCase
{
    /**
     * @test
     */
    public function invalid(): void
    {
        $dom = new ViewDom('<p>');
        $exception = caught(fn() => $dom->findText('.invalid'));
        $this->assertSame('Failed to execute malformed xPath: .invalid', $exception->getMessage());
    }
}
