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

class SigFactory extends AbstractFactory
{
    /**
     * Parse microblog
     *
     * @param string $text
     * @return string
     */
    public function parse(string $text) : string
    {
        start_measure('parsing', 'Parsing signature...');

        $isInCache = $this->isInCache($text);
        if ($isInCache) {
            $text = $this->getFromCache($text);
        }

        if (!$isInCache || $this->isSmiliesAllowed()) {
            $parser = new Parser();

            if (!$isInCache) {
                $text = $this->cache($text, function () use ($parser) {
                    $parser->attach((new SimpleMarkdown($this->app[UserRepositoryInterface::class]))->setBreaksEnabled(true));
                    $parser->attach((new Purifier())->set('HTML.Allowed', 'br,b,strong,i,em,a[href|title|data-user-id],code'));
                    $parser->attach(new Link($this->app[PageRepositoryInterface::class], $this->request->getHost()));
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
