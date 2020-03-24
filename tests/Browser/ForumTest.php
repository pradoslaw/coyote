<?php

namespace Tests\Browser;

use Coyote\Permission;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ForumTest extends DuskTestCase
{
    public function testWriteInLockedCategory()
    {
        $forum = $this->createForum(['is_locked' => true]);

        $this->assertTrue($forum->is_locked);

        $this->browse(function (Browser $browser) use ($forum) {
            try {
                $browser
                    ->visit('/Forum')
                    ->clickLink($forum->name)
                    ->assertDontSee('Nowy wątek')
                    ->visitRoute('forum.topic.submit', [$forum])
                    ->assertSee('401');
            } finally {
                $forum->delete();
            }
        });
    }

    public function testWriteInLockedTopic()
    {
        $forum = $this->createForum();
        $topic = $this->createTopic($forum->id, ['is_locked' => true]);
        $user = $this->createUserWithGroup();

        $this->browse(function (Browser $browser) use ($user, $forum, $topic) {

            $browser
                ->loginAs($user)
                ->visit(UrlBuilder::topic($topic))
                ->assertDontSee('Odpowiedz')
                ->visitRoute('forum.post.submit', [$forum, $topic])
                ->assertSee('401');
        });
    }

    public function testWriteInLockedTopicAsAdmin()
    {
        $user = $this->createUserWithGroup();
        $forum = $this->createForum([], $user->groups()->first()->id);

        $forum->permissions()->create(['value' => 1, 'group_id' => $user->groups()->first()->id, 'permission_id' => Permission::where('name', 'forum-update')->get()->first()->id]);

        $topic = $this->createTopic($forum->id, ['is_locked' => true]);

        $this->browse(function (Browser $browser) use ($user, $forum, $topic) {
            $browser
                ->loginAs($user)
                ->visit(UrlBuilder::topic($topic))
                ->assertSee('Odpowiedz')
                ->clickLink('Odpowiedz')
                ->assertSee('Odpowiedz');
        });
    }

    public function testForumAccess()
    {
        $user = $this->createUserWithGroup();
        $forum = $this->createForum([], $user->groups()->first()->id);

        $this->browse(function (Browser $browser) use ($user, $forum) {
            try {
                $browser
                    ->visit('/Forum')
                    ->assertDontSeeIn('h3', $forum->name)
                    ->visit('/Forum/' . $forum->slug)
                    ->assertSee('401')
                    ->loginAs($user)
                    ->visit('/Forum')
                    ->assertSee($forum->name)
                    ->clickLink($forum->name);
            } finally {
                $forum->delete();
                $user->delete();
            }
        });
    }
}
