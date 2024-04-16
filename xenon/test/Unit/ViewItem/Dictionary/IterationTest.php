<?php
namespace Xenon\Test\Unit\ViewItem\Dictionary;

use PHPUnit\Framework\TestCase;
use Xenon\Field;
use Xenon\ForEach_;
use Xenon\Test\Unit\Fixture;
use Xenon\Xenon;

class IterationTest extends TestCase
{
    use Fixture;

    private Xenon $xenon;

    /**
     * @before
     */
    public function dictionary(): void
    {
        $this->xenon = new Xenon(
            [new ForEach_('dict.key.nest', [new Field('$item')])],
            ['dict' => ['key' => ['nest' => ['value']]]]);
    }

    /**
     * @test
     */
    public function ssr(): void
    {
        $this->assertHtml($this->xenon, 'value');
    }

    /**
     * @test
     */
    public function spa(): void
    {
        $this->assertHtmlRuntime($this->xenon, 'value');
    }
}
