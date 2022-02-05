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
        parent::setUp();

        $this->markdown = $this->app[Markdown::class];
    }

    // tests
    public function testParseUserName()
    {
        $input = $this->markdown->parse('@');
        $this->assertEquals('<p>@</p>', trim($input));

        $input = $this->markdown->parse('@admin lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a> lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>/', trim($input));

        $input = $this->markdown->parse('(@admin)');
        $this->assertMatchesRegularExpression('/\(<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>\)/', trim($input));

        $input = $this->markdown->parse("@admin\n(@admin)");
        $this->assertMatchesRegularExpression("/<a class=\"mention\" data-user-id=\"\d+\" href=\".*\">@admin<\/a><br>\n\(<a class=\"mention\" data-user-id=\"\d+\" href=\".*\">@admin<\/a>\)/", trim($input));

        $input = $this->markdown->parse('@admin lorem ipsum `@admin`');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a> lorem ipsum <code>@admin<\/code>/', trim($input));

        $input = $this->markdown->parse('@admin: lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>: lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin:lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>:lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin, lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>, lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin. lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>. lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin\'s. lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>\'s. lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin @admin');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a> <a class="mention" data-user-id="\d+" href=".*">@admin<\/a>/', trim($input));

        $input = $this->markdown->parse("@admin\n@admin");
        $this->assertMatchesRegularExpression("/<a class=\"mention\" data-user-id=\"\d+\" href=\".*\">@admin<\/a><br>\n<a class=\"mention\" data-user-id=\"\d+\" href=\".*\">@admin<\/a>/", trim($input));

        $input = $this->markdown->parse("@admin… foo");
        $this->assertMatchesRegularExpression("/<a class=\"mention\" data-user-id=\"\d+\" href=\".*\">@admin<\/a>… foo/", $input);

        $now = new \DateTime('now');
        factory(User::class)->create(['name' => 'admin admiński', 'email' => 'foo@bar.com', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('@{admin} lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a> lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@{admin admiński} lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin admiński<\/a> lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@{admin admiński}lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin admiński<\/a>lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@{admin admiński}:lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin admiński<\/a>:lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin admiński: lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a> admiński: lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@{admin-admiński} lorem ipsum');
        $this->assertMatchesRegularExpression('/@admin-admiński lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin-admiński lorem ipsum');
        $this->assertMatchesRegularExpression('/@admin-admiński lorem ipsum/', trim($input));

        factory(User::class)->create(['name' => 'p88.yyy', 'email' => 'foo@bar.com', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('@p88.yyy lorem ipsum');
        $this->assertMatchesRegularExpression('/@p88.yyy lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@{p88.yyy}: lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@p88.yyy<\/a>: lorem ipsum/', trim($input));

        factory(User::class)->create(['name' => 'somedomain', 'email' => 'support@somedomain.net', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('support@somedomain.net');
        $this->assertMatchesRegularExpression('/support@somedomain.net/', trim($input));

        $input = $this->markdown->parse('(@somedomain) lorem ipsum');
        $this->assertMatchesRegularExpression('/\(<a class="mention" data-user-id="\d+" href=".*">@somedomain<\/a>\) lorem ipsum/', trim($input));

        factory(User::class)->create(['name' => 'First(Name)', 'email' => 'bruno@m.com', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('@{First(Name)}: hello');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@First\(Name\)<\/a>: hello/', trim($input));

        $input = $this->markdown->parse('@ 2Ghz');
        $this->assertStringContainsString('@ 2Ghz', trim($input));
    }

    public function testParseLinks()
    {
        $input = '<a href="http://www.google.pl/">http://www.google.pl/</a>';
        $this->assertEquals("<p>$input</p>", trim($this->markdown->parse($input)));

        $input = '<a href="http://www.google.pl/">http://www.google.pl/</a>';
        $this->assertEquals("<p>$input</p>", trim($this->markdown->parse($input)));

        $input = $this->markdown->parse('[http://www.google.pl/](http://www.google.pl/)');
        $this->assertEquals('<p><a href="http://www.google.pl/">http://www.google.pl/</a></p>', trim($input));

        $input = $this->markdown->parse('[test](http://www.google.pl/)');
        $this->assertEquals('<p><a href="http://www.google.pl/">test</a></p>', trim($input));
    }
}
