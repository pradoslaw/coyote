<?php
namespace Xenon\Test\Unit\ViewItem;

use PHPUnit\Framework\TestCase;
use Xenon\Field;
use Xenon\Tag;
use Xenon\Test\Unit\Fixture;
use Xenon\Xenon;

class FieldTest extends TestCase
{
    use Fixture;

    private Xenon $xenon;

    /**
     * @before
     */
    public function state(): void
    {
        $this->xenon = new Xenon(
            [new Tag('div', [], [], [new Field('lorem')])],
            ['lorem' => 'ipsum']);
    }

    /**
     * @test
     */
    public function ssr(): void
    {
        $this->assertHtml($this->xenon, '<div>ipsum</div>');
    }

    /**
     * @test
     */
    public function spa(): void
    {
        $this->assertHtmlRuntime($this->xenon, '<div>ipsum</div>');
    }
}
