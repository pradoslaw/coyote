<?php
namespace Coyote\Domain\Seo\Schema;

use Carbon\Carbon;

class DiscussionForumPosting implements Thing
{
    public function __construct(
        private string  $title,
        private string  $content,
        private string  $url,
        private Carbon  $datePublished,
        private string  $authorUsername,
        private ?string $authorUrl,
        private int     $views,
        private int     $likes,
        private int     $replies)
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
            'author'               => $this->author(),
            'interactionStatistic' => [
                [
                    '@type'                => 'InteractionCounter',
                    'interactionType'      => 'https://schema.org/ViewAction',
                    'userInteractionCount' => $this->views,
                ],
                [
                    '@type'                => 'InteractionCounter',
                    'interactionType'      => 'https://schema.org/LikeAction',
                    'userInteractionCount' => $this->likes,
                ],
                [
                    '@type'                => 'InteractionCounter',
                    'interactionType'      => 'https://schema.org/CommentAction',
                    'userInteractionCount' => $this->replies,
                ],
            ],
        ];
    }

    private function author(): array
    {
        if ($this->authorUrl) {
            return ['@type' => 'Person', 'name' => $this->authorUsername, 'url' => $this->authorUrl];
        }
        return ['@type' => 'Person', 'name' => $this->authorUsername];
    }
}
