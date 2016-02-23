<?php

namespace Coyote\Parser\Scenarios;

use Coyote\Parser\Parser;
use Coyote\Parser\Providers\Geshi;
use Coyote\Parser\Providers\Link;
use Coyote\Parser\Providers\Markdown;
use Coyote\Parser\Providers\Purifier;
use Coyote\Parser\Providers\Smilies;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;

class Pm extends Scenario
{
    /**
     * @var User
     */
    protected $user;

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
        start_measure('parsing', 'Parsing private message...');

        $parser = new Parser();

        // we don't want to cache user's private messages
        $parser->attach((new Markdown($this->user))->setBreaksEnabled(true));
        $parser->attach(new Purifier());
        $parser->attach(new Link());
        $parser->attach(new Geshi());

        if ($this->isSmiliesAllowed()) {
            $parser->attach(new Smilies());
        }

        $text = $parser->parse($text);
        stop_measure('parsing');

        return $text;
    }
}
