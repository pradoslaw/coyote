<?php
namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Services\Parser\CompositeParser;
use Coyote\Services\Parser\Parsers\Markdown;
use Coyote\Services\Parser\Parsers\Prism;
use Coyote\Services\Parser\Parsers\Purifier;
use Coyote\Services\Parser\Parsers\Smilies;

class PmFactory extends AbstractFactory
{
    public function parse(string $text): string
    {
        start_measure('parsing', 'Parsing private message...');

        $parser = new CompositeParser();
        $parser->attach(new Markdown($this->container[UserRepositoryInterface::class], $this->container[PageRepositoryInterface::class]));
        $parser->attach(new Purifier());
        $parser->attach(new Prism());
        if ($this->smiliesAllowed()) {
            $parser->attach(new Smilies());
        }
        $text = $parser->parse($text);
        stop_measure('parsing');

        return $text;
    }
}
