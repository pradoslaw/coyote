<?php

use Coyote\Services\Parser\Parsers\Markdown;

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

        $input = $this->markdown->parse('@admin');
        $this->tester->assertRegExp('/<a href=".*">@admin<\/a>/', $input);

        $input = $this->markdown->parse('(@admin)');
        $this->tester->assertRegExp('/\(<a href=".*">@admin<\/a>\)/', $input);

        $input = $this->markdown->parse("@admin\n(@admin)");
        $this->tester->assertRegExp("/<a href=\".*\">@admin<\/a>\n\(<a href=\".*\">@admin<\/a>\)/", $input);

        $input = $this->markdown->parse('@admin lorem ipsum `@admin`');
        $this->tester->assertRegExp('/<a href=".*">@admin<\/a> lorem ipsum <code>@admin<\/code>/', $input);

        $input = $this->markdown->parse('@admin: lorem ipsum');
        $this->tester->assertRegExp('/<a href=".*">@admin<\/a>: lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin:lorem ipsum');
        $this->tester->assertRegExp('/<a href=".*">@admin<\/a>:lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin, lorem ipsum');
        $this->tester->assertRegExp('/<a href=".*">@admin<\/a>, lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin. lorem ipsum');
        $this->tester->assertRegExp('/<a href=".*">@admin<\/a>. lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin\'s. lorem ipsum');
        $this->tester->assertRegExp('/<a href=".*">@admin<\/a>\'s. lorem ipsum/', $input);

        $now = new \DateTime('now');
        $this->tester->haveRecord('users', ['name' => 'admin admiński', 'email' => 'foo@bar.com', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('@{admin admiński} lorem ipsum');
        $this->tester->assertRegExp('/<a href=".*">@admin admiński<\/a> lorem ipsum/', $input);

        $input = $this->markdown->parse('@{admin admiński}lorem ipsum');
        $this->tester->assertRegExp('/<a href=".*">@admin admiński<\/a>lorem ipsum/', $input);

        $input = $this->markdown->parse('@{admin admiński}:lorem ipsum');
        $this->tester->assertRegExp('/<a href=".*">@admin admiński<\/a>:lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin admiński: lorem ipsum');
        $this->tester->assertRegExp('/<a href=".*">@admin<\/a> admiński: lorem ipsum/', $input);

        $input = $this->markdown->parse('@{admin-admiński} lorem ipsum');
        $this->tester->assertRegExp('/@{admin-admiński} lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin-admiński lorem ipsum');
        $this->tester->assertRegExp('/@admin-admiński lorem ipsum/', $input);

        $this->tester->haveRecord('users', ['name' => 'p88.yyy', 'email' => 'foo@bar.com', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('@p88.yyy lorem ipsum');
        $this->tester->assertRegExp('/@p88.yyy lorem ipsum/', $input);

        $input = $this->markdown->parse('@{p88.yyy}: lorem ipsum');
        $this->tester->assertRegExp('/<a href=".*">@p88.yyy<\/a>: lorem ipsum/', $input);

        $this->tester->haveRecord('users', ['name' => 'somedomain', 'email' => 'support@somedomain.net', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('support@somedomain.net');
        $this->tester->assertRegExp('/support@somedomain.net/', $input);

        $input = $this->markdown->parse('(@somedomain) lorem ipsum');
        $this->tester->assertRegExp('/\(<a href=".*">@somedomain<\/a>\) lorem ipsum/', $input);
    }

    public function testParseLinks()
    {
        $input = '<a href="http://www.google.pl/">http://www.google.pl/</a>';
        $this->tester->assertEquals("<p>$input</p>", $this->markdown->parse($input));

        $input = 'http://google.pl';
        $this->tester->assertEquals("<p>$input</p>", $this->markdown->parse($input));

        $input = $this->markdown->parse('[http://www.google.pl/](http://www.google.pl/)');
        $this->tester->assertEquals('<p><a href="http://www.google.pl/">http://www.google.pl/</a></p>', $input);

        $input = $this->markdown->parse('[test](http://www.google.pl/)');
        $this->tester->assertEquals('<p><a href="http://www.google.pl/">test</a></p>', $input);
    }
}
