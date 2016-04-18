<?php

namespace Coyote\Services\Parser;

use Coyote\Services\Parser\Providers\ProviderInterface;

/**
 * Class Parser
 */
final class Parser
{
    /**
     * @var array
     */
    private $parsers = [];

    /**
     * @param ProviderInterface $parser
     */
    public function attach(ProviderInterface $parser)
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
