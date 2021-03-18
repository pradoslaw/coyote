<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Events\MicroblogSaved;
use Coyote\Events\MicroblogDeleted;
use Coyote\Http\Requests\MicroblogRequest;
use Coyote\Http\Resources\MicroblogResource;
use Coyote\Http\Resources\MicroblogCollection;
use Coyote\Microblog;
use Coyote\Notifications\Microblog\DeletedNotification;
use Coyote\Repositories\Criteria\Microblog\LoadUserScope;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as MicroblogRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Microblog as Stream_Microblog;
use Coyote\Services\Stream\Objects\Comment as Stream_Comment;

class CommentController extends BaseController
{
    /**
     * @var MicroblogRepository
     */
    private $microblog;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @param MicroblogRepository $microblog
     * @param UserRepository $user
     */
    public function __construct(MicroblogRepository $microblog, UserRepository $user)
    {
        parent::__construct();

        $this->microblog = $microblog;
        $this->user = $user;
    }

    /**
     * @param MicroblogRequest $request
     * @param \Coyote\Microblog $microblog
     * @return MicroblogResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(MicroblogRequest $request, ?Microblog $microblog)
    {
        $this->user->pushCriteria(new WithTrashed());

        if (!$microblog->exists) {
            $microblog->user()->associate($this->auth);

            $microblog->fill($request->only(['text', 'parent_id']));
        } else {
            $this->authorize('update', $microblog);

            $microblog->fill($request->only(['text']));
        }

        $isSubscribed = false;

        $this->transaction(function () use ($microblog, &$isSubscribed) {
            $microblog->save();

            // we need to get parent entry only for notification
            $parent = $microblog->parent;

            // map microblog object into stream activity object
            $object = (new Stream_Comment())->map($microblog);
            $target = (new Stream_Microblog())->map($parent);

            if ($microblog->wasRecentlyCreated) {
                // now we can add user to subscribers list (if he's not in there yet)
                // after that he will receive notification about other users comments
                if (!$parent->subscribers()->forUser($this->auth->id)->exists()) {
                    $count = $parent->comments()->forUser($this->auth->id)->count();

                    if ($count === 1) {
                        $parent->subscribers()->create(['user_id' => $this->auth->id]);
                        $isSubscribed = true;
                    }
                } else {
                    $isSubscribed = true;
                }
            }

            // put item into stream activity
            stream($microblog->wasRecentlyCreated ? Stream_Create::class : Stream_Update::class, $object, $target);
        });

        // save broadcast parent entry
        broadcast(new MicroblogSaved($microblog->parent))->toOthers();
        // just broadcast comment
        broadcast(new MicroblogSaved($microblog))->toOthers();

        MicroblogResource::withoutWrapping();

        return (new MicroblogResource($microblog))->additional(['is_subscribed' => (bool) $isSubscribed]);
    }

    /**
     * Usuniecie komentarza z mikrobloga
     *
     * @param \Coyote\Microblog $comment
     */
    public function delete(Microblog $comment)
    {
        abort_if(!$comment->exists, 404);
        $this->authorize('delete', $comment);

        $this->transaction(function () use ($comment) {
            $comment->delete();

            $object = (new Stream_Comment())->map($comment);
            $target = (new Stream_Microblog())->map($comment->parent);

            stream(Stream_Delete::class, $object, $target);
        });

        if ($this->userId !== $comment->user_id) {
            $comment->user->notify(new DeletedNotification($comment, $this->auth));
        }

        event(new MicroblogDeleted($comment));
        event(new MicroblogSaved($comment->parent));
    }

    /**
     * @param int $id
     * @return array
     */
    public function show(int $id)
    {
        if ($this->userId) {
            $this->microblog->pushCriteria(new LoadUserScope($this->auth));
        }

        $comments = $this->microblog->getComments($id);

        MicroblogResource::withoutWrapping();

        // I don't know why I had to call resolve() to return associative array with IDs as keys
        return (new MicroblogCollection($comments))->resolve($this->request);
    }
}
