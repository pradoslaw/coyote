<?php
namespace Neon\Test\BaseFixture\View;

use PHPUnit\Framework\TestCase;
use function Neon\Test\BaseFixture\Caught\caught;

class ViewDomFindManyTest extends TestCase
{
    /**
     * @test
     */
    public function textContents(): void
    {
        $dom = new ViewDom('<ul>
            <li>Ours is the fury</li>
            <li>We do not sow</li>
        <ul>');
        $this->assertThat(
            $dom->findStrings('/html/body/ul/li/text()'),
            $this->equalTo([
                'Ours is the fury',
                'We do not sow',
            ]),
        );
    }

    /**
     * @test
     */
    public function throwForElement(): void
    {
        $dom = new ViewDom('<ul></ul>');
        $exception = caught(fn() => $dom->findStrings('/html/body/ul'));
        $this->assertSame('Failed to get element as string: <ul>', $exception->getMessage());
    }
}
