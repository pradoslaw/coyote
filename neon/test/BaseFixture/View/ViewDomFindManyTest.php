<?php
namespace Neon\Test\BaseFixture\View;

use PHPUnit\Framework\TestCase;

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
            $dom->findMany('/html/body/ul/li'),
            $this->equalTo([
                'Ours is the fury',
                'We do not sow',
            ]),
        );
    }
}
