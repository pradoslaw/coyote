<?php
namespace Coyote\Domain\Administrator\Activity;

use Carbon\Carbon;
use Coyote\Domain\Administrator\View\PostMarkdown;
use Coyote\Domain\Html;

class Post
{
    private PostMarkdown $post;

    public function __construct(
        public string  $text,
        public string  $forumName,
        public string  $forumUrl,
        public string  $topicTitle,
        public string  $postUrl,
        private Carbon $createdAt,
        public bool    $deleted,
        public bool    $isThread,
    )
    {
        $this->post = new PostMarkdown($text);
    }

    public function contentHtml(): Html
    {
        return $this->post->contentHtml();
    }

    public function previewHtml(): Html
    {
        return $this->post->previewHtml();
    }

    public function isLong(): bool
    {
        return $this->post->hasPreview();
    }

    public function dateString(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }
}
