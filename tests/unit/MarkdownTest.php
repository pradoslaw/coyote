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
        $user = new \Coyote\Repositories\Eloquent\UserRepository(app());
        $this->markdown = new Markdown($user);
    }

    protected function _after()
    {
    }

    // tests
    public function testParseUserName()
    {
        $input = $this->markdown->parse('@admin lorem ipsum');
        $this->tester->assertRegExp('/<a href=".*">@admin<\/a> lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin lorem ipsum `@admin`');
        $this->tester->assertRegExp('/<a href=".*">@admin<\/a> lorem ipsum <code>@admin<\/code>/', $input);
    }
}