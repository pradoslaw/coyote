<?php

namespace Tests\Feature;

use Coyote\Forum;
use Coyote\Group;
use Coyote\Guest;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ForumApiTest extends TestCase
{
    use DatabaseTransactions;

    public function testShowAllAccessCategoriesAsAnonymousUser()
    {
        $forum = factory(Forum::class)->create(['order' => 0]);

        $request = $this->get('/v1/forums', ['Accept' => 'application/json']);
        $data = $request->decodeResponseJson();

        $this->assertEquals($forum->name, $data[0]['name']);
        $this->assertNull($data[0]['read_at']);
        $this->assertTrue($data[0]['is_read']);
    }

    public function testDoNotShowRestrictedCategories()
    {
        $user = $this->createUserWithGroup();

        $restricted = factory(Forum::class)->create(['order' => 0]);
        $restricted->access()->create(['group_id' => $user->groups()->first()->id]);

        factory(Forum::class)->create(['order' => 1]);

        $request = $this->get('/v1/forums', ['Accept' => 'application/json']);
        $data = $request->decodeResponseJson();

        $this->assertNotEquals($restricted->name, $data[0]['name']);
    }

    public function testShowCategoryAsUnread()
    {
        $user = factory(User::class)->create();

        Guest::forceCreate(['id' => $user->guest_id, 'updated_at' => now()->subMinute(5)]);

        $forum = factory(Forum::class)->create(['order' => 0]);
        $topic = factory(Topic::class)->create(['forum_id' => $forum->id]);

        $this->actingAs($user, 'api');

        $request = $this->get('/v1/forums', ['Accept' => 'application/json']);
        $data = $request->decodeResponseJson();

        $this->assertEquals($forum->name, $data[0]['name']);
        $this->assertEquals($topic->subject, $data[0]['topic']['subject']);
        $this->assertFalse($data[0]['topic']['is_read']);
        $this->assertNull($data[0]['topic']['read_at']);
        $this->assertFalse($data[0]['is_read']);
        $this->assertNotNull($data[0]['read_at']); // it contains date from guests table

    }
}
