<?php
namespace Coyote\Services\Parser;

use Coyote\Services\Parser\Parsers\Parser;

class CompositeParser
{
    /**
     * @var Parser[]
     */
    private $parsers = [];

    /**
     * @param Parser $parser
     */
    public function attach(Parser $parser)
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
