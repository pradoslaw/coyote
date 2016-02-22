<?php

namespace Coyote\Parser\Scenarios;

use Coyote\Parser\Parser;
use Coyote\Parser\Providers\Censore;
use Coyote\Parser\Providers\Link;
use Coyote\Parser\Providers\Purifier;
use Coyote\Parser\Providers\SimpleMarkdown;
use Coyote\Parser\Providers\Smilies;
use Illuminate\Contracts\Cache\Repository as Cache;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Contracts\WordRepositoryInterface as Word;
use Debugbar;

class Comment extends Scenario
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
     * @param Cache $cache
     * @param User $user
     * @param Word $word
     */
    public function __construct(Cache $cache, User $user, Word $word)
    {
        parent::__construct($cache);

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
        start_measure('parsing', 'Parsing comment...');

        $allowSmilies = auth()->check() && auth()->user()->allow_smilies;
        $isInCache = $this->inCache($text);

        if (!$isInCache || $allowSmilies) {
            $parser = new Parser();

            if (!$this->inCache($text)) {
                $this->cache($text, function () use ($parser) {
                    $parser->attach((new SimpleMarkdown($this->user))->setEnableHashParser(true));
                    $parser->attach((new Purifier())->set('HTML.Allowed', 'b,strong,i,em,a[href|title|data-user-id|class],code'));
                    $parser->attach(new Link());
                    $parser->attach(new Censore($this->word));

                    return $parser;
                });
            } else {
                $text = $this->fromCache($text);
            }

            if (auth()->check() && auth()->user()->allow_smilies) {
                $parser->attach(new Smilies());
            }

            $text = $parser->parse($text);
        }
        stop_measure('parsing');

        return $text;
    }
}
