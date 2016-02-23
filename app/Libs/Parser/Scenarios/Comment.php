<?php

namespace Coyote\Parser\Scenarios;

use Coyote\Parser\Parser;
use Coyote\Parser\Providers\Censore;
use Coyote\Parser\Providers\Link;
use Coyote\Parser\Providers\Purifier;
use Coyote\Parser\Providers\SimpleMarkdown;
use Coyote\Parser\Providers\Smilies;

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
                    $parser->attach((new SimpleMarkdown($this->user))->setEnableHashParser(true));
                    $parser->attach((new Purifier())->set('HTML.Allowed', 'b,strong,i,em,a[href|title|data-user-id|class],code'));
                    $parser->attach(new Link());
                    $parser->attach(new Censore($this->word));

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
