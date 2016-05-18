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
    
    public function testParseInternalAccessors()
    {
        $host = '4programmers.net';
        $this->link = new Link($this->repository, $host);

        $title = 'Forum dyskusyjne';
        $path = '/Discussion_board';

        $now = new \DateTime('now');
        $this->tester->haveRecord('pages', ['title' => $title, 'path' => $path, 'created_at' => $now, 'updated_at' => $now]);
                
        $input = $this->link->parse('[[Discussion board]]');
        $this->tester->assertRegExp("~<a href=\".*" . preg_quote($path) . "\">$title</a>~", $input);

        $input = $this->link->parse('[[Discussion_board]]');
        $this->tester->assertRegExp("~<a href=\".*" . preg_quote($path) . "\">$title</a>~", $input);

        $input = $this->link->parse('[[Discussion board|takie tam forum]]');
        $this->tester->assertRegExp("~<a href=\".*" . preg_quote($path) . "\">takie tam forum</a>~", $input);

        $input = $this->link->parse('[[Discussion board#section|takie tam forum]]');
        $this->tester->assertRegExp("~<a href=\".*" . preg_quote($path) . "#section\">takie tam forum</a>~", $input);

        $input = $this->link->parse('[[Discussion board#section]]');
        $this->tester->assertRegExp("~<a href=\".*" . preg_quote($path) . "#section\">$title</a>~", $input);

        $title = 'Newbie';
        $path = '/Discussion_board/Newbie';

        $now = new \DateTime('now');
        $this->tester->haveRecord('pages', ['title' => $title, 'path' => $path, 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->link->parse('[[Discussion board/Newbie]]');
        $this->tester->assertRegExp("~<a href=\".*" . preg_quote($path) . "\">$title</a>~", $input);

        $input = $this->link->parse('[[Discussion board/Newbie|forum newbie]]');
        $this->tester->assertRegExp("~<a href=\".*" . preg_quote($path) . "\">forum newbie</a>~", $input);
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