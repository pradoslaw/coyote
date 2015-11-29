<?php

namespace Coyote\Parser\Scenarios;

use Coyote\Parser\Parser;
use Coyote\Parser\Providers\Censore;
use Coyote\Parser\Providers\Geshi;
use Coyote\Parser\Providers\Link;
use Coyote\Parser\Providers\Markdown;
use Coyote\Parser\Providers\Purifier;
use Coyote\Parser\Providers\SimpleMarkdown;
use Coyote\Parser\Providers\Smilies;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Contracts\WordRepositoryInterface as Word;
use Debugbar;

class Comment
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Word
     */
    private $word;

    /**
     * @param User $user
     * @param Word $word
     */
    public function __construct(User $user, Word $word)
    {
        $this->user = $user;
        $this->word = $word;
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

        $text = $parser->cache($text, function ($parser) {
            $parser->attach(new SimpleMarkdown($this->user));
            $parser->attach((new Purifier())->set('HTML.Allowed', 'b,strong,i,em,a[href|title|data-user-id],code'));
            $parser->attach(new Link());
            $parser->attach(new Censore($this->word));
        });

        if (auth()->guest() || auth()->user()->allow_smilies) {
            $parser->attach(new Smilies());
        }

        $text = $parser->parse($text);
        Debugbar::stopMeasure('parsing');

        return $text;
    }
}
