<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Administrator\View\Html\CommentMarkdown;
use Coyote\Domain\Administrator\View\Mention;

readonly class CommentMaterial
{
    public CommentMarkdown $content;

    public function __construct(
        string         $contentMarkdown,
        int            $authorId,
        public Mention $authorMention,
        public Date    $createdAt,
        public string  $url,
    )
    {
        $this->content = new CommentMarkdown($contentMarkdown, $authorId);
    }
}
