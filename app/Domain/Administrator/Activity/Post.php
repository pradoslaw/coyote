<?php
namespace Coyote\Domain\Administrator\Activity;

use Carbon\Carbon;
use Coyote\View\Twig\TwigLiteral;

class Post
{
    public function __construct(
        public string  $text,
        public string  $forumName,
        public string  $forumUrl,
        public string  $topicTitle,
        private Carbon $createdAt,
    )
    {
    }

    public function html(): TwigLiteral
    {
        return new TwigLiteral(app('parser.post')->parse($this->text));
    }

    public function isLong(): bool
    {
        return \str_contains($this->text, "\n");
    }

    public function dateString(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }
}
