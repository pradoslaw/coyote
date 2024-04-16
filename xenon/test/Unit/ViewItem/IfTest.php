<?php
namespace Xenon\Test\Unit\ViewItem;

use PHPUnit\Framework\TestCase;
use Xenon\If_;
use Xenon\Tag;
use Xenon\Test\Unit\Fixture;
use Xenon\Text;
use Xenon\Xenon;

class IfTest extends TestCase
{
    use Fixture;

    private function xenonCondition(bool $condition): Xenon
    {
        return new Xenon([
            new Tag('div', [
                new If_('condition', [
                    new Text('Condition is'),
                    new Tag('b', [new Text('true')]),
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
        $this->assertHtml($this->xenonCondition(false), '<div></div>');
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
            '<div></div>');
    }
}
