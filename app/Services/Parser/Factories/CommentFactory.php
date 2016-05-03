<?php

namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Coyote\Services\Parser\Parser;
use Coyote\Services\Parser\Providers\Censore;
use Coyote\Services\Parser\Providers\Link;
use Coyote\Services\Parser\Providers\Purifier;
use Coyote\Services\Parser\Providers\SimpleMarkdown;
use Coyote\Services\Parser\Providers\Smilies;

class CommentFactory extends AbstractFactory
{
    /**
     * Parse comment
     *
     * @param string $text
     * @return string
     */
    public function parse(string $text) : string
    {
        start_measure('parsing', 'Parsing comment...');

        $isInCache = $this->isInCache($text);
        if ($isInCache) {
            $text = $this->getFromCache($text);
        }

        if (!$isInCache || $this->isSmiliesAllowed()) {
            $parser = new Parser();

            if (!$isInCache) {
                $text = $this->cache($text, function () use ($parser) {
                    $parser->attach((new SimpleMarkdown($this->app[UserRepositoryInterface::class]))->setEnableHashParser(true));
                    $parser->attach((new Purifier())->set('HTML.Allowed', 'b,strong,i,em,a[href|title|data-user-id|class],code'));
                    $parser->attach(new Link($this->app[PageRepositoryInterface::class], $this->app['request']));
                    $parser->attach(new Censore($this->app[WordRepositoryInterface::class]));

                    return $parser;
                });
            }

            if ($this->isSmiliesAllowed()) {
                $parser->attach(new Smilies());
                $text = $parser->parse($text);
            }
        }

        stop_measure('parsing');

        return $text;
    }
}
