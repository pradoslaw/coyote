<?php
namespace Coyote\Domain\Seo\Schema;

use Carbon\Carbon;

class DiscussionForumPosting implements Thing
{
    public function __construct(
        private string  $url,
        private string  $title,
        private string  $content,
        private string  $authorUsername,
        private ?string $authorUrl,
        private int     $replies,
        private int     $likes,
        private int     $views,
        private Carbon  $datePublished)
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
                    'interactionType'      => 'https://schema.org/CommentAction',
                    'userInteractionCount' => $this->replies,
                ],
                [
                    '@type'                => 'InteractionCounter',
                    'interactionType'      => 'https://schema.org/LikeAction',
                    'userInteractionCount' => $this->likes,
                ],
                [
                    '@type'                => 'InteractionCounter',
                    'interactionType'      => 'https://schema.org/ViewAction',
                    'userInteractionCount' => $this->views,
                ],
            ],
        ];
    }

    private function author(): array
    {
        $author = ['@type' => 'Person', 'name' => $this->authorUsername];
        if ($this->authorUrl) {
            return $author + ['url' => $this->authorUrl];
        }
        return $author;
    }
}
