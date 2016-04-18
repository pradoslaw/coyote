<?php

namespace Coyote\Services\Parser\Scenarios;

use Coyote\Services\Parser\Parser;
use Coyote\Services\Parser\Providers\Geshi;
use Coyote\Services\Parser\Providers\Link;
use Coyote\Services\Parser\Providers\Markdown;
use Coyote\Services\Parser\Providers\Purifier;
use Coyote\Services\Parser\Providers\Smilies;

class Pm extends Scenario
{
    /**
     * Parse microblog
     *
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        start_measure('parsing', 'Parsing private message...');

        $parser = new Parser();

        // we don't want to cache user's private messages
        $parser->attach((new Markdown($this->app['Coyote\Repositories\Eloquent\UserRepository']))->setBreaksEnabled(true));
        $parser->attach(new Purifier());
        $parser->attach(new Link($this->app['Coyote\Repositories\Eloquent\PageRepository'], $this->app['Illuminate\Http\Request']));
        $parser->attach(new Geshi());

        if ($this->isSmiliesAllowed()) {
            $parser->attach(new Smilies());
        }

        $text = $parser->parse($text);
        stop_measure('parsing');

        return $text;
    }
}
