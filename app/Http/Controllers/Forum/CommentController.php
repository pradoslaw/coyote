<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Events\CommentDeleted;
use Coyote\Events\CommentSaved;
use Coyote\Events\PostSaved;
use Coyote\Events\TopicWasSaved;
use Coyote\Http\Requests\Forum\PostCommentRequest;
use Coyote\Http\Resources\PostCommentResource;
use Coyote\Http\Resources\PostResource;
use Coyote\Notifications\Post\Comment\UserMentionedNotification;
use Coyote\Notifications\Post\CommentedNotification;
use Coyote\Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Http\Controllers\Controller;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Comment as Stream_Comment;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Stream;
use Illuminate\Contracts\Notifications\Dispatcher;
use Coyote\Services\Parser\Helpers\Login as LoginHelper;

class CommentController extends Controller
{
    /**
     * @param PostCommentRequest $request
     * @param Dispatcher $dispatcher
     * @param Post\Comment|null $comment
     * @return PostCommentResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(PostCommentRequest $request, Dispatcher $dispatcher, ?Post\Comment $comment)
    {
        if (!$comment->exists) {
            $comment->user()->associate($this->auth);
            $comment->post_id = $request->input('post_id');
        } else {
            $this->authorize('update', [$comment, $comment->post->forum]);
        }

        // Maybe user does not have an access to this category?
        $this->authorize('access', [$comment->post->forum]);
        // Only moderators can post comment if topic (or forum) was locked
        $this->authorize('write', [$comment]);

        $comment->fill($request->only(['text']));

        $this->transaction(function () use ($comment) {
            $comment->save();

            $target = (new Stream_Topic())->map($comment->post->topic);

            // it is IMPORTANT to parse text first, and then put information to activity stream.
            // so that we will save plan text (without markdown)
            $object = (new Stream_Comment())->map($comment->post, $comment, $comment->post->topic);
            stream($comment->wasRecentlyCreated ? Stream_Create::class : Stream_Update::class, $object, $target);

            if ($comment->wasRecentlyCreated) {
                // subscribe post. notify about all future comments to this post
                $comment->post->subscribe($this->userId, true);
            }
        });

        $comment->setRelation('forum', $comment->post->forum);

        broadcast(new CommentSaved($comment))->toOthers();

        PostCommentResource::withoutWrapping();

        return (new PostCommentResource($comment))->additional(['is_subscribed' => $comment->post->subscribers()->forUser($this->userId)->exists()]);
    }

    /**
     * @param Post\Comment $comment
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(Post\Comment $comment)
    {
        abort_if(!$comment->post, 404);

        $this->authorize('delete', [$comment, $comment->post->forum]);

        $this->transaction(function () use ($comment) {
            $target = (new Stream_Topic())->map($comment->post->topic);
            $object = (new Stream_Comment())->map($comment->post, $comment, $comment->post->topic);

            $comment->delete();

            stream(Stream_Delete::class, $object, $target);
        });

        event(new CommentDeleted($comment));
    }

    /**
     * @param Post $post
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Post $post)
    {
        $this->authorize('access', [$post->forum]);

        PostCommentResource::withoutWrapping();

        $post->load('comments.user');

        $post->comments->each(function (Post\Comment $comment) use ($post) {
            $comment->setRelation('forum', $post->forum);
        });

        return PostCommentResource::collection($post->comments)->keyBy('id');
    }

    public function migrate(Post\Comment $comment, TopicRepositoryInterface $repository)
    {
        $topic = $comment->post->topic;

        // Maybe user does not have an access to this category?
        $this->authorize('access', [$comment->post->forum]);
        // Only moderators can post comment if topic (or forum) was locked
        $this->authorize('write', [$comment]);
        $this->authorize('delete', [$comment, $comment->post->forum]);

        /** @var Post $post */
        $post = $this->transaction(function () use ($topic, $comment, $repository) {
            $stream = Stream::where('object->objectType', 'comment')->where('object->id', $comment->id)->first();

            $post = $topic->posts()->forceCreate(
                array_merge(
                    ['forum_id' => $topic->forum_id, 'topic_id' => $topic->id, 'ip' => $stream->ip, 'browser' => $stream->browser],
                    $comment->only(['created_at', 'text', 'user_id'])
                )
            );

            $comment->delete();
            $repository->adjustReadDate($topic->id, $comment->post->created_at);

            return $post;
        });

        $post->load('assets');
        $tracker = Tracker::make($topic);

        // fire the event. it can be used to index a content and/or add page path to "pages" table
        event(new TopicWasSaved($topic));
        // add post to elasticsearch
        broadcast(new PostSaved($post))->toOthers();

        PostResource::withoutWrapping();

        return (new PostResource($post))->setTracker($tracker)->resolve($this->request);
    }
}
