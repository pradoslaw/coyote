<?php

namespace Coyote\Services\Parser;

use Coyote\Services\Parser\Parsers\ParserInterface;

final class Container
{
    /**
     * @var ParserInterface[]
     */
    private $parsers = [];

    /**
     * @param ParserInterface $parser
     */
    public function attach(ParserInterface $parser)
    {
        $this->parsers[] = $parser;
    }

    /**
     * Remove all parsers from container
     */
    public function detach()
    {
        $this->parsers = [];
    }

    public function parse(string $text): string
    {
        foreach ($this->parsers as $parser) {
            $text = $parser->parse($text);
        }

        return $text;
    }
}
