<?php
namespace Xenon\Test\Unit\ViewItem;

use PHPUnit\Framework\TestCase;
use Xenon\If_;
use Xenon\Tag;
use Xenon\TagField;
use Xenon\Test\Unit\Fixture;
use Xenon\Text;
use Xenon\Xenon;

class IfTest extends TestCase
{
    use Fixture;

    private function xenonCondition(bool $condition): Xenon
    {
        return new Xenon([
            new Tag('div', [], [], [
                new If_('condition',
                    [
                        new Text('Condition is'),
                        new Tag('b', [], [], [new Text('true')]),
                    ], [
                        new Text('Condition is'),
                        new Tag('b', [], [], [new Text('false')]),
                    ]),
            ]),
        ],
            ['condition' => $condition]);
    }

    /**
     * @test
     */
    public function ssrIfTrue(): void
    {
        $this->assertHtml($this->xenonCondition(true), '<div>Condition is<b>true</b></div>');
    }

    /**
     * @test
     */
    public function ssrIfFalse(): void
    {
        $this->assertHtml($this->xenonCondition(false), '<div>Condition is<b>false</b></div>');
    }

    /**
     * @test
     */
    public function spaIfTrue(): void
    {
        $this->executeAndAssertHtmlRuntime(
            $this->xenonCondition(false),
            "xenon.setState('condition', true);",
            '<div>Condition is<b>true</b></div>');
    }

    /**
     * @test
     */
    public function spaIfFalse(): void
    {
        $this->executeAndAssertHtmlRuntime(
            $this->xenonCondition(true),
            "xenon.setState('condition', false);",
            '<div>Condition is<b>false</b></div>');
    }

    /**
     * @test
     */
    public function ssrStateInChildrenBody(): void
    {
        $xenon = new Xenon(
            [new If_('always', [new TagField('b', 'foo')], [])],
            ['always' => true, 'foo' => 'bar']);
        $this->assertHtml($xenon, '<b>bar</b>');
    }

    /**
     * @test
     */
    public function ssrStateInChildrenElse(): void
    {
        $xenon = new Xenon(
            [new If_('never', [], [new TagField('b', 'foo')])],
            ['never' => false, 'foo' => 'bar']);
        $this->assertHtml($xenon, '<b>bar</b>');
    }
}
