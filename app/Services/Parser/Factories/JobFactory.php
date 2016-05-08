<?php

namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Coyote\Services\Parser\Parser;
use Coyote\Services\Parser\Providers\Censore;
use Coyote\Services\Parser\Providers\Geshi;
use Coyote\Services\Parser\Providers\Link;
use Coyote\Services\Parser\Providers\Markdown;
use Coyote\Services\Parser\Providers\Purifier;

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
            $parser = new Parser();

            $text = $this->cache($text, function () use ($parser) {
                $parser->attach((new Markdown($this->app[UserRepositoryInterface::class]))->setBreaksEnabled(true));
                $parser->attach(new Purifier());
                $parser->attach(new Link($this->app[PageRepositoryInterface::class], $this->request->getHost()));
                $parser->attach(new Censore($this->app[WordRepositoryInterface::class]));
                $parser->attach(new Geshi());

                return $parser;
            });
        }
        stop_measure('parsing');

        return $text;
    }
}
