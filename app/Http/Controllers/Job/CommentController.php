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
     * @param Job $job
     * @param int|null $id
     * @return CommentResource
     */
    public function save(CommentRequest $request, Dispatcher $dispatcher, Job $job, int $id = null)
    {
        /** @var Job\Comment $comment */
        $comment = $job->comments()->findOrNew($id);

        if ($comment->exists) {
            $this->checkAbility();
        }

        $comment->fill($request->all())->creating(function ($model) {
            $model->user_id = $this->userId;
        });


        $actor = new Stream_Actor($this->auth);
        if (auth()->guest()) {
            $actor->displayName = $request->input('email');
        }

        $this->transaction(function () use ($comment, $job, $dispatcher, $actor) {
            $comment->save();

            stream(
                $comment->wasRecentlyCreated ? new Stream_Create($actor) : new Stream_Update($actor),
                (new Stream_Comment())->map($job, $comment),
                (new Stream_Job())->map($job)
            );
        });

        if ($comment->wasRecentlyCreated) {
            $subscribers = $job
                ->subscribers()
                ->with('user')
                ->get()
                ->pluck('user') // get all job's subscribers
                ->push($job->user) // push job's author
                ->exceptUser($this->auth); // exclude current logged user

            $dispatcher->send($subscribers, new CommentedNotification($comment));

            if ($comment->parent_id && $comment->user_id !== $comment->parent->user_id) {
                $comment->parent->notify(new RepliedNotification($comment));
            }
        }

        CommentResource::withoutWrapping();
        CommentResource::$job = $job;

        return new CommentResource($comment->load('user'));
    }

    /**
     * @param Job $job
     * @param int $id
     * @throws \Exception
     */
    public function delete(Job $job, int $id)
    {
        $this->checkAbility();

        /** @var Job\Comment $comment */
        $comment = $job->comments()->findOrNew($id);

        $this->transaction(function () use ($comment, $job) {
            $comment->children->each(function ($child) {
                $child->delete();
            });

            $comment->delete();

            stream(
                Stream_Delete::class,
                (new Stream_Comment())->map($job, $comment),
                (new Stream_Job())->map($job)
            );
        });
    }

    private function checkAbility()
    {
        // todo: przeniesc ten kod do policies
        abort_unless($this->userId == $this->request->user()->id || $this->request->user()->can('job-update'), 403);
    }
}
