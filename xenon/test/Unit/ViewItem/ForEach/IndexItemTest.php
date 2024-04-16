<?php
namespace Xenon\Test\Unit\ViewItem\ForEach;

use Tests\Legacy\TestCase;
use Xenon\ForEach_;
use Xenon\TagField;
use Xenon\Test\Unit\Fixture;
use Xenon\Xenon;

class IndexItemTest extends TestCase
{
    use Fixture;

    private Xenon $xenon;

    /**
     * @before
     */
    public function iterationIndexItem(): void
    {
        $this->xenon = new Xenon([
            new ForEach_('values', [
                new TagField('i', '$index'),
                new TagField('b', '$item'),
            ])],
            ['values' => ['foo', 'bar']]);
    }

    /**
     * @test
     */
    public function ssr(): void
    {
        $this->assertHtml($this->xenon, '<i>0</i><b>foo</b><i>1</i><b>bar</b>');
    }

    /**
     * @test
     */
    public function spa(): void
    {
        $this->assertHtmlRuntime($this->xenon, '<i>0</i><b>foo</b><i>1</i><b>bar</b>');
    }
}
