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

    public function testParseInternalLinksWithPolishCharacters()
    {
        $host = '4programmers.net';
        $this->link = new Link($this->repository, $host);

        $title = 'łatwo przyszło, łatwo poszło';
        $path = '/' . str_slug($title);

        $now = new \DateTime('now');
        $this->tester->haveRecord('pages', ['title' => $title, 'path' => $path, 'created_at' => $now, 'updated_at' => $now]);

        $url = 'http://' . $host . $path;
        $this->parse($url, $title);
    }

    public function testParseInternalAccessors()
    {
        $host = '4programmers.net';
        $this->link = new Link($this->repository, $host);

        $title = 'Forum dyskusyjne';
        $path = '/Discussion_board';

        $this->createPage($title, $path);

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

        $this->createPage($title, $path);

        $input = $this->link->parse('[[Discussion board/Newbie]]');
        $this->tester->assertRegExp("~<a href=\".*" . preg_quote($path) . "\">$title</a>~", $input);

        $input = $this->link->parse('[[Discussion board/Newbie|forum newbie]]');
        $this->tester->assertRegExp("~<a href=\".*" . preg_quote($path) . "\">forum newbie</a>~", $input);

        $title = 'Kim jesteśmy?';
        $path = '/Kim_jesteśmy';

        $this->createPage($title, $path);

        $input = $this->link->parse('[[Kim jesteśmy?]]');
        $this->tester->assertRegExp("~<a href=\".*" . preg_quote($path) . "\">" . preg_quote($title) . "</a>~", $input);
    }

    public function testAutolink()
    {
        $parser = new \Coyote\Services\Parser\Parsers\Autolink();

        $input = $parser->parse('http://4programmers.net');
        $this->tester->assertEquals('<a href="http://4programmers.net">http://4programmers.net</a>', $input);

        $input = $parser->parse('to: http://4programmers.net.');
        $this->tester->assertEquals('to: <a href="http://4programmers.net">http://4programmers.net</a>.', $input);

        $input = $parser->parse('to:http://4programmers.net.');
        $this->tester->assertEquals('to:<a href="http://4programmers.net">http://4programmers.net</a>.', $input);

        $input = $parser->parse('<http://4programmers.net>');
        $this->tester->assertEquals('<<a href="http://4programmers.net">http://4programmers.net</a>>', $input);

        $input = $parser->parse('<a href="http://4programmers.net">http://4programmers.net</a>');
        $this->tester->assertEquals('<a href="http://4programmers.net">http://4programmers.net</a>', $input);

        $input = $parser->parse('www.4programmers.net');
        $this->tester->assertEquals('<a href="http://www.4programmers.net">www.4programmers.net</a>', $input);

        $input = $parser->parse('foo@bar.com');
        $this->tester->assertEquals('<a href="mailto:foo@bar.com">foo@bar.com</a>', $input);

        $input = $parser->parse('<foo@bar.com>');
        $this->tester->assertEquals('<<a href="mailto:foo@bar.com">foo@bar.com</a>>', $input);
    }

    private function createPage($title, $path)
    {
        $now = new \DateTime('now');
        $this->tester->haveRecord('pages', [
            'title' => $title, 'path' => $path, 'created_at' => $now, 'updated_at' => $now
        ]);
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
