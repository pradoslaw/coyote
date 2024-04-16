<?php
namespace Xenon\Test\Unit\ViewItem\ForEach;

use PHPUnit\Framework\TestCase;
use Xenon\Field;
use Xenon\ForEach_;
use Xenon\Test\Unit\Fixture;
use Xenon\Xenon;

class StateTest extends TestCase
{
    use Fixture;

    private Xenon $xenon;

    /**
     * @before
     */
    public function field(): void
    {
        $this->xenon = new Xenon(
            [new ForEach_('collection', [new Field('key')])],
            ['collection' => [null], 'key' => 'value']);
    }

    /**
     * @test
     */
    public function ssr(): void
    {
        $this->assertHtml($this->xenon, 'value');
    }
}
