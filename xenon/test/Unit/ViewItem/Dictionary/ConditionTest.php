<?php
namespace Xenon\Test\Unit\ViewItem\Dictionary;

use PHPUnit\Framework\TestCase;
use Xenon\If_;
use Xenon\Test\Unit\Fixture;
use Xenon\Text;
use Xenon\Xenon;

class ConditionTest extends TestCase
{
    use Fixture;

    private Xenon $xenon;

    /**
     * @before
     */
    public function dictionary(): void
    {
        $this->xenon = new Xenon(
            [new If_('dict.key.nest', [new Text('accepted')])],
            ['dict' => ['key' => ['nest' => 'value']]]);
    }

    /**
     * @test
     */
    public function ssr(): void
    {
        $this->assertHtml($this->xenon, 'accepted');
    }

    /**
     * @test
     */
    public function spa(): void
    {
        $this->assertHtmlRuntime($this->xenon, 'accepted');
    }
}
