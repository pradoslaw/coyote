<?php

namespace Tests\Unit\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Services\Parser\Parsers\Markdown;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MarkdownTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var Markdown
     */
    protected $markdown;

    public function setUp(): void
    {
        parent::setUp();;

        $user = app(UserRepositoryInterface::class);
        $this->markdown = new Markdown($user);
    }

    // tests
    public function testParseUserName()
    {
        $input = $this->markdown->parse('@');
        $this->assertEquals('<p>@</p>', $input);

        $input = $this->markdown->parse('@admin lorem ipsum');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin<\/a> lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin<\/a>/', $input);

        $input = $this->markdown->parse('(@admin)');
        $this->assertMatchesRegularExpression('/\(<a href=".*">@admin<\/a>\)/', $input);

        $input = $this->markdown->parse("@admin\n(@admin)");
        $this->assertMatchesRegularExpression("/<a href=\".*\">@admin<\/a>\n\(<a href=\".*\">@admin<\/a>\)/", $input);

        $input = $this->markdown->parse('@admin lorem ipsum `@admin`');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin<\/a> lorem ipsum <code>@admin<\/code>/', $input);

        $input = $this->markdown->parse('@admin: lorem ipsum');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin<\/a>: lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin:lorem ipsum');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin<\/a>:lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin, lorem ipsum');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin<\/a>, lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin. lorem ipsum');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin<\/a>. lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin\'s. lorem ipsum');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin<\/a>\'s. lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin @admin');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin<\/a> <a href=".*">@admin<\/a>/', $input);

        $input = $this->markdown->parse("@admin\n@admin");
        $this->assertMatchesRegularExpression("/<a href=\".*\">@admin<\/a>\n<a href=\".*\">@admin<\/a>/", $input);

//        $input = $this->markdown->parse("@admin… foo");
//        $this->assertMatchesRegularExpression("/<strong>@admin…<\/strong> foo/", $input);

        $now = new \DateTime('now');
        factory(User::class)->create(['name' => 'admin admiński', 'email' => 'foo@bar.com', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('@{admin} lorem ipsum');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin<\/a> lorem ipsum/', $input);

        $input = $this->markdown->parse('@{admin admiński} lorem ipsum');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin admiński<\/a> lorem ipsum/', $input);

        $input = $this->markdown->parse('@{admin admiński}lorem ipsum');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin admiński<\/a>lorem ipsum/', $input);

        $input = $this->markdown->parse('@{admin admiński}:lorem ipsum');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin admiński<\/a>:lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin admiński: lorem ipsum');
        $this->assertMatchesRegularExpression('/<a href=".*">@admin<\/a> admiński: lorem ipsum/', $input);

        $input = $this->markdown->parse('@{admin-admiński} lorem ipsum');
        $this->assertMatchesRegularExpression('/<strong>@admin-admiński<\/strong> lorem ipsum/', $input);

        $input = $this->markdown->parse('@admin-admiński lorem ipsum');
        $this->assertMatchesRegularExpression('/<strong>@admin-admiński<\/strong> lorem ipsum/', $input);

        factory(User::class)->create(['name' => 'p88.yyy', 'email' => 'foo@bar.com', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('@p88.yyy lorem ipsum');
        $this->assertMatchesRegularExpression('/<strong>@p88<\/strong>.yyy lorem ipsum/', $input);

        $input = $this->markdown->parse('@{p88.yyy}: lorem ipsum');
        $this->assertMatchesRegularExpression('/<a href=".*">@p88.yyy<\/a>: lorem ipsum/', $input);

        factory(User::class)->create(['name' => 'somedomain', 'email' => 'support@somedomain.net', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('support@somedomain.net');
        $this->assertMatchesRegularExpression('/support@somedomain.net/', $input);

        $input = $this->markdown->parse('(@somedomain) lorem ipsum');
        $this->assertMatchesRegularExpression('/\(<a href=".*">@somedomain<\/a>\) lorem ipsum/', $input);

        factory(User::class)->create(['name' => 'First(Name)', 'email' => 'bruno@m.com', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('@First(Name): hello');
        $this->assertMatchesRegularExpression('/<a href=".*">@First\(Name\)<\/a>: hello/', $input);

        $input = $this->markdown->parse('@{First(Name)}: hello');
        $this->assertMatchesRegularExpression('/<a href=".*">@First\(Name\)<\/a>: hello/', $input);

        $input = $this->markdown->parse('@ 2Ghz');
        $this->assertStringContainsString('@ 2Ghz', $input);
    }

    public function testParseLinks()
    {
        $input = '<a href="http://www.google.pl/">http://www.google.pl/</a>';
        $this->assertEquals("<p>$input</p>", $this->markdown->parse($input));

        $input = 'http://google.pl';
        $this->assertEquals("<p>$input</p>", $this->markdown->parse($input));

        $input = $this->markdown->parse('[http://www.google.pl/](http://www.google.pl/)');
        $this->assertEquals('<p><a href="http://www.google.pl/">http://www.google.pl/</a></p>', $input);

        $input = $this->markdown->parse('[test](http://www.google.pl/)');
        $this->assertEquals('<p><a href="http://www.google.pl/">test</a></p>', $input);
    }

    public function testParseHashTag()
    {
        $this->markdown->setEnableHashParser(true);

        $input = 'http://4programmers.net#hashtag';
        $this->assertEquals("<p>$input</p>", $this->markdown->parse($input));

        $input = '#';
        $this->assertEquals("<p>$input</p>", $this->markdown->parse($input));

        $input = 'test#';
        $this->assertEquals("<p>$input</p>", $this->markdown->parse($input));

        $input = $this->markdown->parse('#coyote');
        $this->assertMatchesRegularExpression('/<a href=".*">#coyote<\/a>/', $input);

        $input = $this->markdown->parse('(#coyote)');
        $this->assertMatchesRegularExpression('/\(<a href=".*">#coyote<\/a>\)/', $input);

        $input = $this->markdown->parse('#coyote #4programmers.net');
        $this->assertMatchesRegularExpression('/<a href=".*">#coyote<\/a> <a href=".*">#4programmers.net<\/a>/', $input);

        $input = $this->markdown->parse("#coyote\n#4programmers.net");
        $this->assertMatchesRegularExpression("/<a href=\".*\">#coyote<\/a>\n<a href=\".*\">#4programmers.net<\/a>/", $input);
    }
}
