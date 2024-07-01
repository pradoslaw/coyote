<?php
namespace Tests\Unit\UserProfile\Fixture;

use Coyote\Forum;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\Topic;
use Neon\Test\BaseFixture\View\ViewDom;
use Tests\Unit\BaseFixture;

trait UserProfile
{
    use BaseFixture\Forum\Models;

    private function userWithMicroblog(): int
    {
        $userId = $this->userWithPost();
        $this->addMicroblog($userId);
        return $userId;
    }

    private function userProfile(int $userId): string
    {
        return $this->server->get("/Profile/$userId/Post")
            ->assertSuccessful()
            ->content();
    }

    private function userStatistics(string $content): array
    {
        $dom = new ViewDom($content);
        return $dom->findElementsFlatTexts('//ul[@id="box-stats"]/li');
    }

    private function addMicroblog(int $userId): void
    {
        $microblog = new Microblog();
        $microblog->user_id = $userId;
        $microblog->text = 'irrelevant';
        $microblog->save();
    }

    function userWithPost(): int
    {
        $id = $this->models->newUserReturnId();
        $this->storeThread(new Forum, new Topic,
            new Post(['user_id' => $id]));
        return $id;
    }

    function userWithAcceptedAnswer(): int
    {
        $authorId = $this->models->newUserReturnId();
        $post = new Post(['user_id' => $authorId]);
        $topic = $this->storeThread(new Forum, new Topic, $post);
        $topic->accept()->create(['post_id' => $post->id, 'user_id' => $this->models->newUserReturnId()]);
        return $authorId;
    }
}
