<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Events\PostWasDeleted;
use Coyote\Events\PostWasSaved;
use Coyote\Http\Resources\PostResource;
use Coyote\Post;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Stream\Activities\Merge as Stream_Merge;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\UrlBuilder\UrlBuilder;

class MergeController extends BaseController
{
    /**
     * @param Post $post
     * @return PostResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Post $post)
    {
        $this->authorize('merge', $post->forum);

        /** @var \Coyote\Post $previous */
        $previous = $this->transaction(function () use ($post) {
            return $this->post->merge($this->userId, $post);
        });

        // add post to elasticsearch
        event(new PostWasSaved($previous));
        // remove from elasticsearch
        event(new PostWasDeleted($post));

        $url = UrlBuilder::topic($post->topic);

        $object = (new Stream_Post(['url' => $url]))->map($post);
        $target = (new Stream_Topic())->map($post->topic);

        stream(Stream_Merge::class, $object, $target);
        stream(Stream_Delete::class, $object, $target);

        PostResource::withoutWrapping();
        $tracker = Tracker::make($post->topic);

        $previous->comments->each(function (Post\Comment $comment) use ($post) {
            $comment->setRelation('forum', $post->forum);
        });

        return (new PostResource($previous))->setTracker($tracker)->setSigParser(app('parser.sig'));
    }
}
