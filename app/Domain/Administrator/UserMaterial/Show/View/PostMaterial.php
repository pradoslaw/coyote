<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Administrator\View\PostMarkdown;
use Coyote\Domain\Html;

readonly class PostMaterial
{
    public Html $content;

    /**
     * @param HistoryItem[] $history
     */
    public function __construct(
        public Link   $forum,
        public Link   $topic,
        public string $url,
        public Date   $createdAt,
        public Person $author,
        string        $contentMarkdown,
        public array  $history,
    )
    {
        $this->content = new PostMarkdown($contentMarkdown);
    }
}
