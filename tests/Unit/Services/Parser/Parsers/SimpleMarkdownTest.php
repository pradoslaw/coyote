<?php

namespace Tests\Unit\Services\Parser\Parsers;

use Coyote\Services\Parser\Parsers\SimpleMarkdown;
use Tests\TestCase;

class SimpleMarkdownTest extends TestCase
{
    public function testDoNotChangeLineBreaks()
    {
        $markdown = $this->app[SimpleMarkdown::class];

        $input = "one\ntwo\nthree";
        $this->assertEquals($input, trim($markdown->parse($input)));
    }

    public function testAutolinkExtension()
    {
        $markdown = $this->app[SimpleMarkdown::class];

        $link = 'https://docs.djangoproject.com/en/2.0/#first-steps';
        $this->assertEquals("<a href=\"$link\">$link</a>", trim($markdown->parse($link)));
    }
}
