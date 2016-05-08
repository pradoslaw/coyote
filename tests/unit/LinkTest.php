<?php

use Coyote\Services\Parser\Parsers\Link;
use Faker\Factory;

class LinkTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var Link
     */
    protected $link;

    /**
     * @var mixed
     */
    protected $repository;

    protected function _before()
    {
        $this->repository = new \Coyote\Repositories\Eloquent\PageRepository(app());
    }

    protected function _after()
    {
    }

    // tests
    public function testParseInternalLinks()
    {
        $host = '4programmers.net';
        $this->link = new Link($this->repository, $host);

        $fake = Factory::create();

        $title = $fake->title;
        $path = '/Forum/' . str_slug($title);

        $now = new \DateTime('now');
        $this->tester->haveRecord('pages', ['title' => $title, 'path' => $path, 'created_at' => $now, 'updated_at' => $now]);

        $url = 'http://' . $host . $path;
        $this->parse($url, $title);

        $host = 'dev.4programmers.net';
        $this->link = new Link($this->repository, $host);

        $url = 'http://' . $host . $path;
        $this->parse($url, $title);
    }

    private function parse($url, $title)
    {
        $input = $this->link->parse(link_to($url));
        $this->tester->assertEquals("<a href=\"$url\">$title</a>", $input);

        $input = $this->link->parse(link_to($url, $url, [], true));
        $this->tester->assertEquals("<a href=\"$url\">$title</a>", $input);

        $input = $this->link->parse(link_to($url, 'lorem ipsum'));
        $this->tester->assertEquals("<a href=\"$url\">lorem ipsum</a>", $input);
    }
}