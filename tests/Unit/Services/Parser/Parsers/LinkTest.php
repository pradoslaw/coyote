<?php

namespace Tests\Unit\Services\Parser\Parsers;

use Coyote\Page;
use Coyote\Repositories\Eloquent\PageRepository;
use Coyote\Services\Parser\Parsers\Link;
use Faker\Factory;
use Tests\TestCase;

class LinkTest extends TestCase
{
    /**
     * @var Link
     */
    protected $link;

    /**
     * @var \Collective\Html\HtmlBuilder
     */
    protected $htmlBuilder;

    /**
     * @var mixed
     */
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new PageRepository(app());
        $this->htmlBuilder = app('html');
    }

    // tests
    public function testParseInternalLinks()
    {
        $host = '4programmers.net';
        $this->link = new Link($this->repository, $host, $this->htmlBuilder);

        $fake = Factory::create();

        $title = $fake->title;
        $path = '/Forum/' . str_slug($title);

        $now = new \DateTime('now');
        Page::forceCreate(['title' => $title, 'path' => $path, 'created_at' => $now, 'updated_at' => $now]);

        $url = 'http://' . $host . $path;
        $this->parse($url, $title);

        $host = 'dev.4programmers.net';
        $this->link = new Link($this->repository, $host, $this->htmlBuilder);

        $url = 'http://' . $host . $path;
        $this->parse($url, $title);
    }

    public function testParseInternalAccessors()
    {
        $host = '4programmers.net';
        $this->link = new Link($this->repository, $host, $this->htmlBuilder);

        $title = 'Forum dyskusyjne';
        $path = '/Discussion_board';

        $this->createPage($title, $path);

        $input = $this->link->parse('[[Discussion board]]');
        $this->assertMatchesRegularExpression("~<a href=\".*" . preg_quote($path) . "\">$title</a>~", $input);

        $input = $this->link->parse('[[Discussion_board]]');
        $this->assertMatchesRegularExpression("~<a href=\".*" . preg_quote($path) . "\">$title</a>~", $input);

        $input = $this->link->parse('[[Discussion board|takie tam forum]]');
        $this->assertMatchesRegularExpression("~<a href=\".*" . preg_quote($path) . "\">takie tam forum</a>~", $input);

        $input = $this->link->parse('[[Discussion board#section|takie tam forum]]');
        $this->assertMatchesRegularExpression("~<a href=\".*" . preg_quote($path) . "#section\">takie tam forum</a>~", $input);

        $input = $this->link->parse('[[Discussion board#section]]');
        $this->assertMatchesRegularExpression("~<a href=\".*" . preg_quote($path) . "#section\">$title</a>~", $input);

        $title = 'Newbie';
        $path = '/Discussion_board/Newbie';

        $this->createPage($title, $path);

        $input = $this->link->parse('[[Discussion board/Newbie]]');
        $this->assertMatchesRegularExpression("~<a href=\".*" . preg_quote($path) . "\">$title</a>~", $input);

        $input = $this->link->parse('[[Discussion board/Newbie|forum newbie]]');
        $this->assertMatchesRegularExpression("~<a href=\".*" . preg_quote($path) . "\">forum newbie</a>~", $input);

        $title = 'Kim jesteśmy?';
        $path = '/Kim_jesteśmy';

        $this->createPage($title, $path);

        $input = $this->link->parse('[[Kim jesteśmy?]]');
        $this->assertMatchesRegularExpression("~<a href=\".*" . preg_quote($path) . "\">" . preg_quote($title) . "</a>~", $input);

        $input = $this->link->parse('<code>[[Kim jesteśmy?]]</code>');
        $this->assertStringContainsString("<code>[[Kim jesteśmy?]]</code>", $input);

        $input = $this->link->parse('<pre><code>[[Kim jesteśmy?]]</code></pre>');
        $this->assertStringContainsString("<pre><code>[[Kim jesteśmy?]]</code></pre>", $input);
    }

    public function testAutolinkLongUrl()
    {
        $parser = new Link($this->repository, '4programmers.net', $this->htmlBuilder);

        $input = $parser->parse('https://scrutinizer-ci.com/g/adam-boduch/coyote/inspections/8778b728-ef73-4167-8092-424a57a8e66d');
        $this->assertEquals('<a href="https://scrutinizer-ci.com/g/adam-boduch/coyote/inspections/8778b728-ef73-4167-8092-424a57a8e66d">https://scrutinizer-ci.com/g/[...]8-ef73-4167-8092-424a57a8e66d</a>', $input);
    }

    private function createPage($title, $path)
    {
        $now = new \DateTime('now');
        Page::forceCreate([
            'title' => $title, 'path' => $path, 'created_at' => $now, 'updated_at' => $now
        ]);
    }

    private function parse($url, $title)
    {
        $input = $this->link->parse(link_to($url));
        $this->assertEquals("<a href=\"$url\">$title</a>", $input);

        $input = $this->link->parse(link_to($url, $url, [], true));
        $this->assertEquals("<a href=\"$url\">$title</a>", $input);

        $input = $this->link->parse(link_to($url, 'lorem ipsum'));
        $this->assertEquals("<a href=\"$url\">lorem ipsum</a>", $input);
    }
}
