<?php

namespace Coyote\Parser\Scenarios;

use Coyote\Parser\Parser;
use Coyote\Parser\Providers\Geshi;
use Coyote\Parser\Providers\Link;
use Coyote\Parser\Providers\Markdown;
use Coyote\Parser\Providers\Purifier;
use Coyote\Parser\Providers\Smilies;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Debugbar;

class Pm
{
    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Parse microblog
     *
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        Debugbar::startMeasure('parsing', 'Time for parsing');

        $parser = new Parser();
        $parser->setEnableCache(false);

        $text = $parser->cache($text, function ($parser) {
            $parser->attach((new Markdown($this->user))->setBreaksEnabled(true));
            $parser->attach(new Purifier());
            $parser->attach(new Link());
            $parser->attach(new Geshi());
        });

        if (auth()->check() && auth()->user()->allow_smilies) {
            $parser->attach(new Smilies());
        }

        $text = $parser->parse($text);
        Debugbar::stopMeasure('parsing');

        return $text;
    }
}
