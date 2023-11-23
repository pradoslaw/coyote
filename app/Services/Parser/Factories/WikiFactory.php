<?php
namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WikiRepositoryInterface;
use Coyote\Services\Parser\CompositeParser;
use Coyote\Services\Parser\Parsers\Context;
use Coyote\Services\Parser\Parsers\Latex;
use Coyote\Services\Parser\Parsers\Markdown;
use Coyote\Services\Parser\Parsers\Prism;
use Coyote\Services\Parser\Parsers\Purifier;
use Coyote\Services\Parser\Parsers\Template;

class WikiFactory extends AbstractFactory
{
    public function parse(string $text): string
    {
        start_measure('parsing', 'Parsing wiki...');

        $parser = new CompositeParser();

        $text = $this->cache($text, function () use ($parser) {
            $allowedTags = explode(',', config('purifier')['HTML.Allowed']);
            unset($allowedTags['ul']);

            // we add those tags for backward compatibility
            $allowedTags[] = 'div[class]';
            $allowedTags[] = 'ul[class]';
            $allowedTags[] = 'h1';

            $parser->attach(new Template($this->container[WikiRepositoryInterface::class]));
            $parser->attach(new Markdown($this->container[UserRepositoryInterface::class], $this->container[PageRepositoryInterface::class]));
            $parser->attach(new Latex());
            $parser->attach(new Purifier($allowedTags));
            $parser->attach(new Context());
            $parser->attach(new Prism());

            return $parser;
        });

        stop_measure('parsing');

        return $text;
    }
}
