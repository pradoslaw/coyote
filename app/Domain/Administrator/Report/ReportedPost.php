<?php
namespace Coyote\Domain\Administrator\Report;

use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Administrator\View\Mention;
use Coyote\Domain\Administrator\View\PostMarkdown;
use Coyote\Domain\Html;

class ReportedPost
{
    public Mention $authorMention;

    private PostMarkdown $content;

    public function __construct(
        public int    $id,
        public string $contentMarkdown,
        public int    $authorId,
        public string $authorName,
        public string $authorAvatar,
        public array  $reporterIds,
        public array  $reporterNames,
        public array  $reportTypes,
        public Date   $createdAt,
        public Date   $updatedAt,
        public int    $forumId,
        public string $forumSlug,
    )
    {
        $this->content = new PostMarkdown($this->contentMarkdown);
        $this->authorMention = new Mention($this->authorId, $this->authorName);
    }

    public function url(): string
    {
        return \route('adm.flag.show', [$this->id]);
    }

    public function updatedAgo(): string
    {
        return $this->updatedAt->timeAgo();
    }

    public function createdAgo(): string
    {
        return $this->createdAt->timeAgo();
    }

    public function reporterMentions(): array
    {
        return \array_map(
            fn(int $id, string $name) => new Mention($id, $name),
            $this->reporterIds, $this->reporterNames,
        );
    }

    public function html(): Html
    {
        return $this->content->contentHtml();
    }

    public function preview(): Html
    {
        return $this->content->previewHtml();
    }

    public function forumUrl(): string
    {
        return route('forum.category', [$this->forumSlug]);
    }
}
