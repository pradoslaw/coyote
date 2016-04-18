<?php

namespace Coyote\Services\Parser\Scenarios;

use Coyote\Services\Parser\Parser;
use Coyote\Services\Parser\Providers\Censore;
use Coyote\Services\Parser\Providers\Geshi;
use Coyote\Services\Parser\Providers\Link;
use Coyote\Services\Parser\Providers\Markdown;
use Coyote\Services\Parser\Providers\Purifier;
use Coyote\Services\Parser\Providers\Smilies;

class Post extends Scenario
{
    /**
     * Parse post
     *
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        start_measure('parsing', 'Parsing post...');

        $isInCache = $this->isInCache($text);
        if ($isInCache) {
            $text = $this->getFromCache($text);
        }

        if (!$isInCache || $this->isSmiliesAllowed()) {
            $parser = new Parser();

            if (!$isInCache) {
                $text = $this->cache($text, function () use ($parser) {
                    $parser->attach((new Markdown($this->app['Coyote\Repositories\Eloquent\UserRepository']))->setBreaksEnabled(true));
                    $parser->attach(new Purifier());
                    $parser->attach(new Link($this->app['Coyote\Repositories\Eloquent\PageRepository'], $this->app['Illuminate\Http\Request']));
                    $parser->attach(new Censore($this->app['Coyote\Repositories\Eloquent\WordRepository']));
                    $parser->attach(new Geshi());

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
