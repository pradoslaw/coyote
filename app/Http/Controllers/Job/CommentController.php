<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\Job\CommentRequest;
use Coyote\Job;
use Illuminate\Contracts\Notifications\Dispatcher;

class CommentController extends Controller
{
    public function edit(Job $job, ?int $id)
    {
        return $job->comments()->findOrNew($id);
    }

    public function save(Dispatcher $dispatcher, CommentRequest $request, Job $job, ?int $id)
    {
        $comment = $job->comments()->findOrNew($id);

        $comment->fill($request->all())->creating(function ($model) {
            $model->user_id = $this->userId;
        });

        $this->transaction(function () use ($comment) {
            $comment->save();
        });

        return $comment;
    }
}
