<?php

namespace Coyote\Services\Parser\Scenarios;

use Coyote\Services\Parser\Parser;
use Coyote\Services\Parser\Providers\Censore;
use Coyote\Services\Parser\Providers\Link;
use Coyote\Services\Parser\Providers\Purifier;
use Coyote\Services\Parser\Providers\SimpleMarkdown;
use Coyote\Services\Parser\Providers\Smilies;

class Comment extends Scenario
{
    /**
     * Parse comment
     *
     * @param string $text
     * @return string
     */
    public function parse($text)
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
                    $parser->attach((new SimpleMarkdown($this->app['Coyote\Repositories\Eloquent\UserRepository']))->setEnableHashParser(true));
                    $parser->attach((new Purifier())->set('HTML.Allowed', 'b,strong,i,em,a[href|title|data-user-id|class],code'));
                    $parser->attach(new Link($this->app['Coyote\Repositories\Eloquent\PageRepository'], $this->app['Illuminate\Http\Request']));
                    $parser->attach(new Censore($this->app['Coyote\Repositories\Eloquent\WordRepository']));

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
