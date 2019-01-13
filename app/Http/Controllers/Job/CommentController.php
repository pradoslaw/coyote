<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\Job\CommentRequest;
use Coyote\Http\Resources\CommentResource;
use Coyote\Job;
use Illuminate\Contracts\Notifications\Dispatcher;

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
        $comment = $job->comments()->findOrNew($id);

        $comment->fill($request->all())->creating(function ($model) {
            $model->user_id = $this->userId;
        });

        $this->transaction(function () use ($comment) {
            $comment->save();
        });

        CommentResource::withoutWrapping();

        return new CommentResource($comment->load('user'));
    }
}
