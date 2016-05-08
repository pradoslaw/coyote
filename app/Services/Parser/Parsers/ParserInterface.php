<?php

namespace Coyote\Services\Parser\Parsers;

/**
 * Interface ParserInterface
 */
interface ParserInterface
{
    /**
     * @param string $text
     * @return mixed
     */
    public function parse($text);
}
