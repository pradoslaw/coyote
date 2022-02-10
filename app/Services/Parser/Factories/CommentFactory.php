<?php

namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Coyote\Services\Parser\Container;
use Coyote\Services\Parser\Parsers\Censore;
use Coyote\Services\Parser\Parsers\Emphasis;
use Coyote\Services\Parser\Parsers\Purifier;
use Coyote\Services\Parser\Parsers\SimpleMarkdown;
use Coyote\Services\Parser\Parsers\Smilies;

class CommentFactory extends AbstractFactory
{
    /**
     * permission that is required for comment's author to run Emphasis parser
     */
    const PERMISSION = 'forum-emphasis';

    protected array $htmlTags = ['b', 'strong', 'i', 'em', 'del', 'a[href|title|data-user-id|class]', 'code'];
    protected ?int $userId = null;

    /**
     * Set comment's author ID.
     *
     * @param int $userId
     * @return $this
     */
    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Parse comment
     *
     * @param string $text
     * @return string
     */
    public function parse(string $text) : string
    {
        start_measure('parsing', get_class($this));

        $this->cache->setId(class_basename($this) . $this->userId);

        $parser = new Container();

        $text = $this->cache($text, function () use ($parser) {
            $parser->attach(new SimpleMarkdown($this->app[UserRepositoryInterface::class], $this->app[PageRepositoryInterface::class]));

            $parser->attach(
                (new Purifier())->set('HTML.Allowed', implode(',', $this->htmlTags))
            );
            $parser->attach(new Censore($this->app[WordRepositoryInterface::class]));

            if (!empty($this->userId)) {
                $parser->attach(
                    (new Emphasis($this->app[UserRepositoryInterface::class]))
                        ->setUserId($this->userId)
                        ->setAbility(self::PERMISSION)
                );
            }

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
