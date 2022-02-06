<?php

namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Coyote\Services\Parser\Container;
use Coyote\Services\Parser\Parsers\Censore;
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

        $parser = new Container();

        $text = $this->cache($text, function () use ($parser) {
            $parser->attach(new Purifier());
            $parser->attach(new Censore($this->app[WordRepositoryInterface::class]));

            return $parser;
        });

        stop_measure('parsing');

        return $text;
    }
}
