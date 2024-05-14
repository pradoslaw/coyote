<?php
namespace Tests\Unit\UserProfile\Fixture;

use Coyote\Forum;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Neon\Test\BaseFixture\View\ViewDom;

trait UserProfile
{
    private function userWithMicroblog(): User
    {
        $user = $this->userWithPost();
        $this->addMicroblog($user);
        return $user;
    }

    private function userProfile(User $user): string
    {
        return $this->server->get("/Profile/$user->id/Post")
            ->assertSuccessful()
            ->content();
    }

    private function userStatistics(string $content): array
    {
        $dom = new ViewDom($content);
        return $dom->findElementsFlatTexts('//ul[@id="box-stats"]/li');
    }

    private function addMicroblog(User $user): void
    {
        $microblog = new Microblog();
        $microblog->user_id = $user->id;
        $microblog->text = 'irrelevant';
        $microblog->save();
    }

    function userWithPost(): User
    {
        $user = $this->newUser();
        $this->storeThread(new Forum, new Topic,
            new Post(['user_id' => $user->id]));
        return $user;
    }

    function userWithAcceptedAnswer(): User
    {
        $author = $this->newUser();
        $post = new Post(['user_id' => $author->id]);
        $topic = $this->storeThread(new Forum, new Topic, $post);
        $accepter = $this->newUser();
        $topic->accept()->create(['post_id' => $post->id, 'user_id' => $accepter->id]);
        return $author;
    }

    function newUser(): User
    {
        $user = new User;
        $user->name = 'irrelevant' . \uniqId();
        $user->email = 'irrelevant';
        $user->save();
        return $user;
    }
}
