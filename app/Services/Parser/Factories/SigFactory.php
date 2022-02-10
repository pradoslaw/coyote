<?php

namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Coyote\Services\Parser\Container;
use Coyote\Services\Parser\Parsers\Censore;
use Coyote\Services\Parser\Parsers\Purifier;
use Coyote\Services\Parser\Parsers\SimpleMarkdown;
use Coyote\Services\Parser\Parsers\Smilies;

class SigFactory extends AbstractFactory
{
    protected array $htmlTags = ['b', 'strong', 'i', 'em', 'del', 'a[href|title|data-user-id|class]', 'code', 'br'];

    public function parse(string $text) : string
    {
        start_measure('parsing', get_class($this));

        $parser = new Container();

        $text = $this->cache($text, function () use ($parser) {
            $markdown = new SimpleMarkdown($this->app[UserRepositoryInterface::class], $this->app[PageRepositoryInterface::class]);
            $markdown->setConfig([
                'renderer' => [
                    'soft_break'      => "<br>\n",
                ]
            ]);
            $parser->attach($markdown);

            $parser->attach(
                (new Purifier())->set('HTML.Allowed', implode(',', $this->htmlTags))
            );
            $parser->attach(new Censore($this->app[WordRepositoryInterface::class]));

            return $parser;
        });

        if ($this->isSmiliesAllowed()) {
            $parser->attach(new Smilies());
            $text = $parser->parse($text);
        }

        stop_measure('parsing');

        return $text;
    }
}
