<?php

namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Services\Parser\Parser;
use Coyote\Services\Parser\Providers\Geshi;
use Coyote\Services\Parser\Providers\Link;
use Coyote\Services\Parser\Providers\Markdown;
use Coyote\Services\Parser\Providers\Purifier;
use Coyote\Services\Parser\Providers\Smilies;

class PmFactory extends AbstractFactory
{
    /**
     * Parse microblog
     *
     * @param string $text
     * @return string
     */
    public function parse(string $text) : string
    {
        start_measure('parsing', 'Parsing private message...');

        $parser = new Parser();

        // we don't want to cache user's private messages
        $parser->attach((new Markdown($this->app[UserRepositoryInterface::class]))->setBreaksEnabled(true));
        $parser->attach(new Purifier());
        $parser->attach(new Link($this->app[PageRepositoryInterface::class], $this->app['request']));
        $parser->attach(new Geshi());

        if ($this->isSmiliesAllowed()) {
            $parser->attach(new Smilies());
        }

        $text = $parser->parse($text);
        stop_measure('parsing');

        return $text;
    }
}
