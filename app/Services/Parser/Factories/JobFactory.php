<?php

namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Coyote\Services\Parser\Container;
use Coyote\Services\Parser\Parsers\Censore;
use Coyote\Services\Parser\Parsers\Geshi;
use Coyote\Services\Parser\Parsers\Link;
use Coyote\Services\Parser\Parsers\Markdown;
use Coyote\Services\Parser\Parsers\Purifier;

class JobFactory extends AbstractFactory
{
    /**
     * Parse post
     *
     * @param string $text
     * @return string
     */
    public function parse(string $text) : string
    {
        start_measure('parsing', 'Parsing job data...');

        $isInCache = $this->isInCache($text);
        if ($isInCache) {
            $text = $this->getFromCache($text);
        } else {
            $parser = new Container();

            $text = $this->cache($text, function () use ($parser) {
                $parser->attach(new Purifier());
                $parser->attach(new Link($this->app[PageRepositoryInterface::class], $this->request->getHost()));
                $parser->attach(new Censore($this->app[WordRepositoryInterface::class]));

                return $parser;
            });
        }
        stop_measure('parsing');

        return $text;
    }
}
