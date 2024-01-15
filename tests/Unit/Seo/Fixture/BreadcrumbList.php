<?php
namespace Tests\Unit\Seo\Fixture;

use Tests\Unit\Seo;

trait BreadcrumbList
{
    use Seo\Fixture\Schema, Seo\Fixture\Models;

    function breadcrumbsSchema(): array
    {
        return $this->schema('/Forum', type:'BreadcrumbList');
    }

    function categorySchema(string $forumName, string $forumSlug): array
    {
        $this->newForum($forumName, $forumSlug);
        return $this->schema("/Forum/$forumSlug", 'BreadcrumbList');
    }

    function topicSchema(string $topicTitle, string $forumSlug): array
    {
        $topicId = $this->newThread($topicTitle, $forumSlug);
        return [
            $this->schema("/Forum/$forumSlug/$topicId", 'BreadcrumbList'),
            $topicId,
        ];
    }
}
