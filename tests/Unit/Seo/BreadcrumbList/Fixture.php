<?php
namespace Tests\Unit\Seo\BreadcrumbList;

use Tests\Unit\Seo;

trait Fixture
{
    use Seo\Fixture\Schema, Seo\BreadcrumbList\Models;

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
