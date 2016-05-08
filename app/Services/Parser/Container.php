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
     *
     */
    public function detach()
    {
        $this->parsers = [];
    }

    /**
     * @param $text
     * @return mixed
     */
    public function parse($text)
    {
        foreach ($this->parsers as $parser) {
            $text = $parser->parse($text);
        }

        return $text;
    }
}
