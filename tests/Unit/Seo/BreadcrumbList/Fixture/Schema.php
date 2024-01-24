<?php
namespace Tests\Unit\Seo\BreadcrumbList\Fixture;

use Tests\Unit\Seo;

trait Schema
{
    use Seo\Fixture\Schema, Seo\BreadcrumbList\Fixture\Models;

    function breadcrumbsSchema(): array
    {
        return $this->schema('/Forum', type:'BreadcrumbList');
    }

    function categorySchema(string $forumName, string $forumSlug): array
    {
        $this->newForum($forumName, $forumSlug);
        return $this->schema("/Forum/$forumSlug", 'BreadcrumbList');
    }

    function categoryWithParentSchema(string $parentName, string $parentSlug): array
    {
        $childSlug = $this->newChildForum($parentName, $parentSlug);
        return $this->schema("/Forum/$childSlug", 'BreadcrumbList');
    }

    function topicSchema(string $topicTitle, string $forumSlug): array
    {
        $topic = $this->newThread($topicTitle, $forumSlug);
        return [
            $this->schema("/Forum/$forumSlug/{$topic->id}-{$topic->slug}", 'BreadcrumbList'),
            $topic->id,
        ];
    }
}
