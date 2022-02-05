<?php

namespace Coyote\Services\Parser\Parsers;

interface ParserInterface
{
    public function parse(string $text): string;
}
