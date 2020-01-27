<?php

namespace Tests\Browser;

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

    public function testForumAccess()
    {
        $user = $this->createUserWithGroup();
        $forum = $this->createForum([], $user->groups()->first()->id);

        $this->browse(function (Browser $browser) use ($user, $forum) {
            try {
                $browser
                    ->visit('/Forum')
                    ->assertDontSee($forum->name)
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
