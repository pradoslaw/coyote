<?php

namespace Tests\Unit\Services;

use Coyote\Forum;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Tests\TestCase;

class UrlBuilderTest extends TestCase
{
    public function testBuildTopicUrlWithEmptySlug()
    {
        $topic = factory(Topic::class)->state('id')->make(['slug' => '']);

        $this->assertStringContainsString("/Forum/" . $topic->forum->slug . "/{$topic->id}-", UrlBuilder::topic($topic));
    }
}
