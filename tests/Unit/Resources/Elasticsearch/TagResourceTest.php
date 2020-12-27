<?php

namespace Tests\Unit\Resources\Elasticsearch;

use Coyote\Http\Resources\Elasticsearch\TagResource;
use Coyote\Tag;
use Tests\TestCase;

class TagResourceTest extends TestCase
{
    public function testMapModel()
    {
        $tag = factory(Tag::class)->make(['topics' => 1, 'microblogs' => 2, 'jobs' => 3]);

        TagResource::withoutWrapping();

        $resource = TagResource::make($tag)->toResponse(request())->getData(true);

        $this->assertEquals($tag->name, $resource['name']);
        $this->assertEquals($tag->real_name, $resource['real_name']);
        $this->assertEquals($tag->topics, $resource['topics']);
        $this->assertEquals($tag->microblogs, $resource['microblogs']);
        $this->assertEmpty($resource['logo']);
    }
}
