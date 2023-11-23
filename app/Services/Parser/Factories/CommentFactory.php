<?php

namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Coyote\Services\Parser\CompositeParser;
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

    protected array $htmlTags = ['b', 'strong', 'i', 'u', 'em', 'del', 'a[href|title|data-user-id|class]', 'code'];
    protected ?int $userId = null;

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function parse(string $text): string
    {
        start_measure('parsing', get_class($this));

        $this->cache->setId(class_basename($this) . $this->userId);

        $parser = new CompositeParser();

        $text = $this->parseAndCache($text, function () use ($parser) {
            $parser->attach(new SimpleMarkdown($this->container[UserRepositoryInterface::class], $this->container[PageRepositoryInterface::class]));
            $parser->attach(new Purifier($this->htmlTags));
            $parser->attach(new Censore($this->container[WordRepositoryInterface::class]));

            if (!empty($this->userId)) {
                $parser->attach(
                    (new Emphasis($this->container[UserRepositoryInterface::class]))
                        ->setUserId($this->userId)
                        ->setAbility(self::PERMISSION)
                );
            }

            return $parser;
        });

        if ($this->smiliesAllowed()) {
            $parser->attach(new Smilies());
            $text = $parser->parse($text);
        }

        stop_measure('parsing');

        return $text;
    }
}
