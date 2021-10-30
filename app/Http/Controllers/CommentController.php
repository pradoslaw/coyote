<?php

namespace Coyote\Http\Controllers;

use Coyote\Http\Requests\CommentRequest;
use Coyote\Http\Resources\CommentResource;
use Coyote\Comment;
use Coyote\Notifications\Job\CommentedNotification;
use Coyote\Notifications\Job\RepliedNotification;
use Coyote\Services\Stream\Actor as Stream_Actor;
use Illuminate\Contracts\Notifications\Dispatcher;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Comment as Stream_Comment;

class CommentController extends Controller
{
    /**
     * @param CommentRequest $request
     * @param Dispatcher $dispatcher
     * @param Comment|null $comment
     * @return CommentResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(CommentRequest $request, Dispatcher $dispatcher, Comment $comment = null)
    {
        $this->authorize('update', $comment);

        $comment->fill($request->all())->creating(function (Comment $model) use ($request) {
            $model->user_id = $this->userId;
            $model->forceFill($request->only(['resource_id', 'resource_type']));
        });

        if ($comment->parent_id) {
            $comment->forceFill($comment->parent->only(['resource_id', 'resource_type']));
        }

        $actor = new Stream_Actor($this->auth);

        $this->transaction(function () use ($comment, $dispatcher, $actor) {
            $comment->save();
            $target = $this->target($comment);

            stream(
                $comment->wasRecentlyCreated ? new Stream_Create($actor) : new Stream_Update($actor),
                (new Stream_Comment())->comment($comment),
                (new $target)->map($comment->resource)
            );
        });

        if ($comment->wasRecentlyCreated) {
            $subscribers = $comment
                ->resource
                ->subscribers()
                ->with('user')
                ->get()
                ->pluck('user') // get all subscribers
                ->exceptUser($this->auth); // exclude current logged user

            $dispatcher->send($subscribers, new CommentedNotification($comment));

//            if ($comment->parent_id && $comment->user_id !== $comment->parent->user_id) {
//                $comment->parent->notify(new RepliedNotification($comment));
//            }
        }

        CommentResource::withoutWrapping();

        return new CommentResource($comment->load(['user', 'children']));
    }

    public function delete(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $this->transaction(function () use ($comment) {
            $comment->children->each(function ($child) {
                $child->delete();
            });

            $comment->delete();
            $target = $this->target($comment);

            stream(
                Stream_Delete::class,
                (new Stream_Comment())->comment($comment),
                (new $target)->map($comment->resource)
            );
        });
    }

    private function target(Comment $comment): string
    {
        return 'Coyote\\Services\\Stream\\Objects\\' . class_basename($comment->resource_type);
    }
}
