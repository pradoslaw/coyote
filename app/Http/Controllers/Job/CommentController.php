<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\Job\CommentRequest;
use Coyote\Http\Resources\CommentResource;
use Coyote\Job;
use Coyote\Notifications\Job\CommentedNotification;
use Coyote\Notifications\Job\RepliedNotification;
use Coyote\Services\Stream\Actor as Stream_Actor;
use Illuminate\Contracts\Notifications\Dispatcher;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Services\Stream\Objects\Comment as Stream_Comment;

class CommentController extends Controller
{
    /**
     * @param CommentRequest $request
     * @param Dispatcher $dispatcher
     * @param Job\Comment|null $comment
     * @return CommentResource
     */
    public function save(CommentRequest $request, Dispatcher $dispatcher, Job\Comment $comment = null)
    {
        if ($comment->exists) {
            $this->checkAbility();
        }

        $comment->fill($request->all())->creating(function (Job\Comment $model) {
            $model->user_id = $this->userId;

            if ($model->parent_id) {
                $model->job_id = $model->parent->job_id;
            }
        });

        $actor = new Stream_Actor($this->auth);

        $this->transaction(function () use ($comment, $dispatcher, $actor) {
            $comment->save();

            stream(
                $comment->wasRecentlyCreated ? new Stream_Create($actor) : new Stream_Update($actor),
                (new Stream_Comment())->map($comment->job, $comment),
                (new Stream_Job())->map($comment->job)
            );
        });

        if ($comment->wasRecentlyCreated) {
            $subscribers = $comment
                ->job
                ->subscribers()
                ->with('user')
                ->get()
                ->pluck('user') // get all job's subscribers
                ->push($comment->job->user) // push job's author
                ->exceptUser($this->auth); // exclude current logged user

            $dispatcher->send($subscribers, new CommentedNotification($comment));

            if ($comment->parent_id && $comment->user_id !== $comment->parent->user_id) {
                $comment->parent->notify(new RepliedNotification($comment));
            }
        }

        CommentResource::withoutWrapping();

        return new CommentResource($comment->load('user'));
    }

    /**
     * @param Job\Comment $comment
     */
    public function delete(Job\Comment $comment)
    {
        $this->checkAbility();

        $this->transaction(function () use ($comment) {
            $comment->children->each(function ($child) {
                $child->delete();
            });

            $comment->delete();

            stream(
                Stream_Delete::class,
                (new Stream_Comment())->map($comment->job, $comment),
                (new Stream_Job())->map($comment->job)
            );
        });
    }

    private function checkAbility()
    {
        // todo: przeniesc ten kod do policies
        abort_unless($this->userId == $this->request->user()->id || $this->request->user()->can('job-update'), 403);
    }
}
