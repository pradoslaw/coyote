<?php

namespace Tests\Legacy\IntegrationOld\Models\Microblog;

use Coyote\Microblog;
use Coyote\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\IntegrationOld\TestCase;

class TagTest extends TestCase
{
    use DatabaseTransactions;

    public function testToggleTagAndCheckCounter()
    {
        $tag = factory(Tag::class)->create();

        /** @var Microblog $microblog */
        $microblog = factory(Microblog::class)->create();
        $microblog->tags()->sync([$tag->id]);

        $tag->refresh();

        $this->assertEquals(1, $tag->resources['Coyote\Microblog']);

        $microblog->tags()->sync([]);

        $tag->refresh();

        $this->assertEquals(0, $tag->resources['Coyote\Microblog']);
    }
}
