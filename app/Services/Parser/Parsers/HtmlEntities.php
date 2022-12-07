<?php

namespace Coyote\Services\Parser\Parsers;

class HtmlEntities implements ParserInterface
{
    public function parse(string $text): string
    {
        return htmlentities($text);
    }
}
