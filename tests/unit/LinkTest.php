<?php

use Coyote\Parser\Providers\Link;
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

    protected $request;

    protected function _before()
    {
        $repository = new \Coyote\Repositories\Eloquent\PageRepository(app());
        $this->link = new Link($repository, request());

        $this->request = request();
    }

    protected function _after()
    {
    }

    // tests
    public function testParseInternalLinks()
    {
        $fake = Factory::create();

        $title = $fake->title;
        $path = '/Forum/' . str_slug($title);

        $url = 'http://' . $this->request->getHost() . $path;

        $now = new \DateTime('now');
        $this->tester->haveRecord('pages', ['title' => $title, 'path' => $path, 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->link->parse(link_to($url));
        $this->tester->assertEquals("<a href=\"$url\">$title</a>", $input);

        $input = $this->link->parse(link_to($url, $url, [], true));
        $this->tester->assertEquals("<a href=\"$url\">$title</a>", $input);

        $input = $this->link->parse(link_to($url, 'lorem ipsum'));
        $this->tester->assertEquals("<a href=\"$url\">lorem ipsum</a>", $input);
    }
}