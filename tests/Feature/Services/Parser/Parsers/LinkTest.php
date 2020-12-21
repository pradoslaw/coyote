<?php

namespace Tests\Feature\Services\Parser\Parsers;

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

    public function testParseInternalLinksWithPolishCharacters()
    {
        $host = '4programmers.net';
        $this->link = new Link($this->repository, $host, $this->htmlBuilder);

        $title = 'łatwo przyszło, łatwo poszło';
        $path = '/' . str_slug($title);

        $now = new \DateTime('now');
        Page::forceCreate(['title' => $title, 'path' => $path, 'created_at' => $now, 'updated_at' => $now]);

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

    public function testYoutubeVideos()
    {
        $parser = new Link($this->repository, '4programmers.net', $this->htmlBuilder);

        $this->assertStringContainsString('iframe', $parser->parse('https://www.youtube.com/watch?v=7dU3ybPqV94'));
        $this->assertStringContainsString('iframe', $parser->parse(link_to('https://www.youtube.com/watch?v=7dU3ybPqV94')));
        $this->assertStringContainsString('iframe', $parser->parse(link_to('https://www.youtube.com/watch?v=7dU3ybPqV94#foo')));
        $this->assertStringContainsString('iframe', $parser->parse('https://youtu.be/enOjqwOE1ec'));
        $this->assertStringContainsString('iframe', $parser->parse('https://www.youtu.be/enOjqwOE1ec'));
        $this->assertStringNotContainsString('iframe', $parser->parse('https://youtu.be/'));
        $this->assertStringContainsString('iframe', $parser->parse(link_to('https://youtu.be/enOjqwOE1ec')));

        $this->assertEquals('<a href="https://www.youtube.com/watch?v=SC9ybxMDGlE">test</a>', $parser->parse('<a href="https://www.youtube.com/watch?v=SC9ybxMDGlE">test</a>'));
        $this->assertStringContainsString('iframe', $parser->parse('<a href="https://www.youtube.com/watch?v=SC9ybxMDGlE">https://www.youtube.com/watch?v=SC9ybxMDGlE</a>'));

        $this->assertStringContainsString('https://www.youtube.com/watch?v=7dU3ybPqV94', $parser->parse('<code>https://www.youtube.com/watch?v=7dU3ybPqV94</code>'));

        $this->assertStringContainsString('https://youtube.com/embed/vd0zDG4vwOw?start=1107', $parser->parse('https://youtu.be/vd0zDG4vwOw?t=18m27s'));
        $this->assertStringContainsString('https://youtube.com/embed/vd0zDG4vwOw?start=1107', $parser->parse('https://www.youtube.com/watch?v=vd0zDG4vwOw#t=18m27s'));
    }

    public function testAutolink()
    {
        $parser = new Link($this->repository, '4programmers.net', $this->htmlBuilder);

        $input = $parser->parse('http://4programmers.net');
        $this->assertEquals('<a href="http://4programmers.net">http://4programmers.net</a>', $input);

        $input = $parser->parse('to: http://4programmers.net.');
        $this->assertEquals('to: <a href="http://4programmers.net">http://4programmers.net</a>.', $input);

        $input = $parser->parse('to:http://4programmers.net.');
        $this->assertEquals('to:<a href="http://4programmers.net">http://4programmers.net</a>.', $input);

        $input = $parser->parse('<http://4programmers.net>');
        $this->assertEquals('<<a href="http://4programmers.net">http://4programmers.net</a>>', $input);

        $input = $parser->parse('<a href="http://4programmers.net">http://4programmers.net</a>');
        $this->assertEquals('<a href="http://4programmers.net">http://4programmers.net</a>', $input);

        $input = $parser->parse('www.4programmers.net');
        $this->assertEquals('<a href="http://www.4programmers.net">www.4programmers.net</a>', $input);

        $input = $parser->parse('foo@bar.com');
        $this->assertEquals('<a href="mailto:foo@bar.com">foo@bar.com</a>', $input);

        $input = $parser->parse('<foo@bar.com>');
        $this->assertEquals('<<a href="mailto:foo@bar.com">foo@bar.com</a>>', $input);

        $input = '@4programmers.net';
        $this->assertEquals($input, $parser->parse($input));

        $input = '<a href="http://4programmers.net">4programmers</a>.net';
        $this->assertEquals($input, $parser->parse($input));

        $input = 'www.4programmers.net';
        $this->assertEquals('<a href="http://www.4programmers.net">www.4programmers.net</a>', $parser->parse($input));

        $input = 'asp.net';
        $this->assertEquals('asp.net', $parser->parse($input));

        $input = 'asp.net/foobar';
        $this->assertEquals('<a href="http://asp.net/foobar">asp.net/foobar</a>', $parser->parse($input));

        $link = 'http://pl.wikipedia.org/wiki/normalna_(bazy_danych)';
        $this->assertEquals("<a href=\"$link\">$link</a>", $parser->parse($link));
    }

    public function testAutolinkLongUrl()
    {
        $parser = new Link($this->repository, '4programmers.net', $this->htmlBuilder);

        $input = $parser->parse('https://scrutinizer-ci.com/g/adam-boduch/coyote/inspections/8778b728-ef73-4167-8092-424a57a8e66d');
        $this->assertEquals('<a href="https://scrutinizer-ci.com/g/adam-boduch/coyote/inspections/8778b728-ef73-4167-8092-424a57a8e66d">https://scrutinizer-ci.com/g/[...]8-ef73-4167-8092-424a57a8e66d</a>', $input);

        $title = '"Kompetentność" uczących się programowania';
        $path = '/Forum/Spolecznosc/266098-kompetentnosc_uczacych_sie_programowania';

        $this->createPage($title, $path);

        $input = $parser->parse('http://4programmers.net/Forum/Spolecznosc/266098-kompetentnosc_uczacych_sie_programowania');
        $this->assertEquals('<a href="http://4programmers.net/Forum/Spolecznosc/266098-kompetentnosc_uczacych_sie_programowania">&quot;Kompetentność&quot; uczących się programowania</a>', $input);
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
