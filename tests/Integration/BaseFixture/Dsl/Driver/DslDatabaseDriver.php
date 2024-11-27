<?php
namespace Tests\Integration\BaseFixture\Dsl\Driver;

use Coyote\Forum;
use Tests\Integration\BaseFixture\Dsl\Model\DslTopic;
use Tests\Integration\BaseFixture\Server\Laravel;

class DslDatabaseDriver
{
    public function __construct(private Laravel\TestCase $laravel) {}

    public function seedCategoryIfNotExists(string $categorySlug): void
    {
        Forum::query()->firstOrCreate(
            ['slug' => $categorySlug],
            [
                'slug'             => $categorySlug,
                'name'             => $categorySlug,
                'description'      => '',
                'parent_id'        => null,
                'section'          => '',
                'enable_anonymous' => false,
            ]);
    }

    public function assertTopicColumn(DslTopic $topic, string $columnName, bool $expectedValue): void
    {
        $this->laravel->assertSeeInDatabase('topics', [
            'id'        => $topic->id,
            'title'     => $topic->title,
            $columnName => $expectedValue,
        ]);
    }

    public function assertTopicExists(DslTopic $topic): void
    {
        $this->laravel->assertSeeInDatabase('topics', [
            'id'    => $topic->id,
            'title' => $topic->title,
        ]);
    }

    public function assertTopicNotExists(DslTopic $topic): void
    {
        $this->laravel->assertDatabaseRecordNotExists('topics', [
            'id'    => $topic->id,
            'title' => $topic->title,
        ]);
    }
}
