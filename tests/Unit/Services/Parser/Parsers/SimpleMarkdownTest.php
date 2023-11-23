<?php

namespace Tests\Unit\Services\Parser\Parsers;

use Coyote\Repositories\Eloquent\PageRepository;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\Parser\Parsers\SimpleMarkdown;
use Tests\TestCase;

class SimpleMarkdownTest extends TestCase
{
    public function testDoNotChangeLineBreaks()
    {
        $markdown = new SimpleMarkdown($this->app[UserRepository::class], $this->app[PageRepository::class], 'host');

        $input = "one\ntwo\nthree";
        $this->assertEquals($input, trim($markdown->parse($input)));
    }

    public function testAutolinkExtension()
    {
        $markdown = new SimpleMarkdown($this->app[UserRepository::class], $this->app[PageRepository::class], 'host');

        $link = 'https://docs.djangoproject.com/en/2.0/#first-steps';
        $this->assertEquals("<a href=\"$link\">$link</a>", trim($markdown->parse($link)));
    }
}
