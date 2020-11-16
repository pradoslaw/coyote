<?php

namespace Tests\Unit\Models\Topic;

use Coyote\Tag;
use Coyote\Topic;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TagTest extends TestCase
{
    use DatabaseTransactions;

    public function testToggleTagAndCheckCounter()
    {
        $tag = factory(Tag::class)->create();

        /** @var Topic $topic */
        $topic = factory(Topic::class)->create();
        $topic->tags()->sync([$tag->id]);

        $tag->refresh();

        $this->assertEquals(1, $tag->topics);

        $topic->tags()->sync([]);

        $tag->refresh();

        $this->assertEquals(0, $tag->topics);
    }
}
