<?php

namespace Coyote\Parser\Scenarios;

use Coyote\Parser\Parser;
use Coyote\Parser\Providers\Censore;
use Coyote\Parser\Providers\Geshi;
use Coyote\Parser\Providers\Link;
use Coyote\Parser\Providers\Markdown;
use Coyote\Parser\Providers\Purifier;
use Coyote\Parser\Providers\Smilies;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Contracts\WordRepositoryInterface as Word;
use Debugbar;

class Microblog
{
    private $user;
    private $word;

    public function __construct(User $user, Word $word)
    {
        $this->user = $user;
        $this->word = $word;
    }

    public function parse($text)
    {
        Debugbar::startMeasure('parsing', 'Time for parsing');

        $parser = new Parser();

        $text = $parser->cache($text, function ($parser) {
            $parser->attach((new Markdown($this->user))->setEnableHashParser(true));
            $parser->attach(new Purifier());
            $parser->attach(new Link());
            $parser->attach(new Censore($this->word));
            $parser->attach(new Geshi());
        });

        $parser->attach(new Smilies());

        $text = $parser->parse($text);
        Debugbar::stopMeasure('parsing');

        return $text;
    }
}
