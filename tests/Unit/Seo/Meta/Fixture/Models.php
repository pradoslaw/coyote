<?php
namespace Tests\Unit\Seo\Meta\Fixture;

use Coyote\Forum;
use Coyote\Topic;
use Tests\Unit\BaseFixture;

trait Models
{
    use BaseFixture\Forum\Store;

    function newTopic(): array
    {
        $topic = $this->storeThread(new Forum, new Topic);
        return [$topic->forum->slug, "{$topic->id}-{$topic->slug}"];
    }
}
