<?php

use Coyote\Parser\Providers\Markdown;

class MarkdownTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var Markdown
     */
    protected $markdown;

    protected function _before()
    {
        $this->markdown = new Markdown();
    }

    protected function _after()
    {
    }

    // tests
    public function testParseUserName()
    {
        //
    }
}