<?php

namespace Tests\Browser;

use Coyote\Permission;
use Coyote\Services\UrlBuilder;
use Coyote\User;
use Faker\Factory;
use Laravel\Dusk\Browser;

class ForumTest extends DuskTestCase
{
    public function testShowValidateErrors()
    {
        $forum = $this->createForum(['require_tag' => true]);
        $user = $this->createUserWithGroup();

        $this->browse(function (Browser $browser) use ($forum, $user) {
            try {
                $browser
                    ->loginAs($user)
                    ->visitRoute('forum.topic.submit', ['forum' => $forum])
                    ->type('title', 'ab')
                    ->pressAndWaitFor('Zapisz')
                    ->assertSee('Tytuł jest zbyt krótki. Musi mieć minimum 3 znaki długości.')
                    ->assertSee('Proszę wpisać treść.')
                    ->assertSee('Wymagane jest przypisanie minimum jednego tagu do tego wątku.');
            } finally {
                $forum->delete();
            }
        });
    }

    public function testWriteTopic()
    {
        $forum = $this->createForum();
        $user = factory(User::class)->create(['reputation' => 5000]);

        $this->browse(function (Browser $browser) use ($forum, $user) {
            $faker = Factory::create();

            $browser
                ->loginAs($user)
                ->visitRoute('forum.topic.submit', ['forum' => $forum])
                ->waitFor('.editor')
                ->type('tags', $tag = $faker->word)
                ->keys('input[name="tags"]', '{space}')
                ->keys('input[name="tags"]', '{escape}')
                ->pause(500)
                ->click('input[name="title"]')
                ->type('title', $title = $faker->text(50))
                ->type('.cm-content', $text = $faker->realText())
                ->press('Zapisz')
                ->waitForText('Tak, jestem pewien')
                ->press('Tak, jestem pewien')
                ->waitForText($title, 15)
                ->assertSee($title)
                ->assertSee($text)
                ->assertSee($tag);
        });
    }

    public function testWriteInLockedCategory()
    {
        $forum = $this->createForum(['is_locked' => true, 'name' => 'some locked category']);

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
            try {
                $browser
                    ->loginAs($user)
                    ->visit(UrlBuilder::topic($topic))
                    ->assertDontSee('Odpowiedz');
            } finally {
                $topic->forceDelete();
                $forum->delete();
                $user->delete();
            }
        });
    }

    public function testWriteInLockedTopicAsAdmin()
    {
        $user = $this->createUserWithGroup();
        $forum = $this->createForum([], $user->groups()->first()->id);

        $forum->permissions()->create(['value' => 1, 'group_id' => $user->groups()->first()->id, 'permission_id' => Permission::where('name', 'forum-update')->get()->first()->id]);

        $topic = $this->createTopic($forum->id, ['is_locked' => true]);

        $this->browse(function (Browser $browser) use ($user, $forum, $topic) {
            $faker = Factory::create();
            $text = $faker->text;

            try {
                $browser
                    ->loginAs($user)
                    ->visit(UrlBuilder::topic($topic))
                    ->waitFor('.editor')
                    ->assertSee('Odpowiedz')
                    ->clickLink('Odpowiedz')
                    ->with('#js-submit-form', function ($form) use ($text) {
                        $form->type('.cm-content', $text)
                            ->press('Zapisz');
                    })
                    ->waitForText($text);
            } finally {
                $topic->forceDelete();
                $forum->delete();
                $user->delete();
            }
        });
    }

    public function testForumAccess()
    {
        $user = $this->createUserWithGroup();
        $forum = $this->createForum([], $user->groups()->first()->id);

        $this->browse(function (Browser $browser) use ($user, $forum): void {
            try {
                $browser
                    ->visit('/Forum')
                    ->assertDontSeeIn('h3', $forum->name)
                    ->visit('/Forum/' . $forum->slug)
                    ->assertSee('403')
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
