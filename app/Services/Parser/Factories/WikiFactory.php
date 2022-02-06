<?php

namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WikiRepositoryInterface;
use Coyote\Services\Parser\Container;
use Coyote\Services\Parser\Parsers\Context;
use Coyote\Services\Parser\Parsers\Prism;
use Coyote\Services\Parser\Parsers\Latex;
use Coyote\Services\Parser\Parsers\Markdown;
use Coyote\Services\Parser\Parsers\Purifier;
use Coyote\Services\Parser\Parsers\Template;

class WikiFactory extends AbstractFactory
{
    /**
     * Parse post
     *
     * @param string $text
     * @return string
     */
    public function parse(string $text) : string
    {
        start_measure('parsing', 'Parsing wiki...');

        $parser = new Container();

        $text = $this->cache($text, function () use ($parser) {
            $allowedTags = explode(',', config('purifier')['HTML.Allowed']);
            unset($allowedTags['ul']);

            // we add those tags for backward compatibility
            $allowedTags[] = 'div[class]';
            $allowedTags[] = 'ul[class]';
            $allowedTags[] = 'h1';

            $parser->attach(new Template($this->app[WikiRepositoryInterface::class]));
            $parser->attach(new Markdown($this->app[UserRepositoryInterface::class], $this->app[PageRepositoryInterface::class]));
            $parser->attach(new Latex());
            $parser->attach((new Purifier())->set('HTML.Allowed', implode(',', $allowedTags)));
            $parser->attach(new Context());
            $parser->attach(new Prism());

            return $parser;
        });

        stop_measure('parsing');

        return $text;
    }
}
