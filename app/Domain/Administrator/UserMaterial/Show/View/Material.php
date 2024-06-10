<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Administrator\View\PostMarkdown;
use Coyote\Domain\Html;

readonly class Material
{
    private PostMarkdown $content;

    /**
     * @param HistoryItem[] $history
     */
    public function __construct(
        public Link   $forum,
        public Link   $topic,
        public Date   $createdAt,
        public Person $author,
        string        $contentMarkdown,
        public array  $history,
    )
    {
        $this->content = new PostMarkdown($contentMarkdown);
    }

    public function content(): Html
    {
        return $this->content->contentHtml();
    }
}
