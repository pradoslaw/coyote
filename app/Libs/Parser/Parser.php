<?php

namespace Coyote\Parser;

use Coyote\Parser\Providers\ProviderInterface;

class Parser
{
    protected $parsers = [];

    public function attach(ProviderInterface $parser)
    {
        $this->parsers[] = $parser;
    }

    public function detach($observer)
    {
        //
    }

    public function parse($text)
    {
        foreach ($this->parsers as $parser) {
            $text = $parser->parse($text);
        }

        return $text;
    }
}
