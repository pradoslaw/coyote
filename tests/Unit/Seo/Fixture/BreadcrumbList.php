<?php
namespace Tests\Unit\Seo\Fixture;

use Coyote\Forum;
use Tests\Unit\BaseFixture\Laravel;
use Tests\Unit\Seo\Fixture;

trait BreadcrumbList
{
    use Fixture\Schema, Laravel\Transactional;

    function breadcrumbsSchema(): array
    {
        return $this->schema('/Forum', type:'BreadcrumbList');
    }

    function categoryBreadcrumbsSchema(string $forumName, string $forumSlug): array
    {
        $this->newForum($forumName, $forumSlug);
        return $this->schema("/Forum/$forumSlug", 'BreadcrumbList');
    }

    function newForum(string $name, string $slug): void
    {
        $forum = new Forum([
            'name'        => $name,
            'slug'        => $slug,
            'description' => 'irrelevant',
        ]);
        $forum->save();
    }
}
