<?php
namespace Coyote\Domain\Seo\Schema;

use Carbon\Carbon;

class DiscussionForumPosting implements Thing
{
    public function __construct(
        private string $url,
        private string $title,
        private string $content,
        private string $authorUsername,
        private int    $replies,
        private Carbon $datePublished)
    {
    }

    public function schema(): array
    {
        return [
            '@context'             => 'https://schema.org',
            '@type'                => 'DiscussionForumPosting',
            '@id'                  => $this->url,
            'url'                  => $this->url,
            'headline'             => $this->title,
            'text'                 => $this->content,
            'datePublished'        => $this->datePublished->toIso8601String(),
            'author'               => [
                '@type' => 'Person',
                'name'  => $this->authorUsername,
            ],
            'interactionStatistic' => [
                '@type'                => 'InteractionCounter',
                'userInteractionCount' => $this->replies,
            ],
        ];
    }
}
