<?php
namespace Coyote\Services;

use Coyote\Post;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Actor as Stream_Actor;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Database\Connection;

readonly class PostService
{
    public function __construct(private Connection $connection) {}

    public function createPost(
        int    $topicId,
        string $markdownText,
        int    $userId,
        string $postIp,
        string $postUserAgent,

    ): void
    {
        $this->createPostInTopic(
            Topic::query()->findOrFail($topicId),
            User::query()->findOrFail($userId),
            $markdownText,
            $postIp,
            $postUserAgent);
    }

    private function createPostInTopic(
        Topic  $topic,
        User   $user,
        string $markdownText,
        string $postIp,
        string $postUserAgent,
    ): void
    {
        $post = new Post();
        $post->ip = $postIp;
        $post->browser = $postUserAgent;
        $post->text = $markdownText;
        $post->user()->associate($user);
        $post->topic()->associate($topic);
        $post->forum()->associate($topic->forum);
        $this->store($user, $post, $topic);
    }

    private function store(User $user, Post $post, Topic $topic): void
    {
        $this->connection->transaction(function () use ($user, $post, $topic) {
            $post->save();
            $post->subscribe($user->id, true);
            if ($user->allow_subscribe) {
                $topic->subscribe($user->id, true);
            }
            stream(
                new Stream_Create(new Stream_Actor($user)),
                new Stream_Post(['url' => UrlBuilder::post($post)])->map($post),
                new Stream_Topic()->map($topic),
            );
        });
    }
}
