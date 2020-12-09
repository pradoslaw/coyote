<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Events\MicroblogWasDeleted;
use Coyote\Events\MicroblogSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Http\Requests\MicroblogRequest;
use Coyote\Http\Resources\MicroblogResource;
use Coyote\Microblog;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Services\Parser\Helpers\Hash as HashHelper;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Microblog as Stream_Microblog;
use Illuminate\Contracts\Notifications\Dispatcher;

/**
 * Class SubmitController
 * @package Coyote\Http\Controllers\Microblog
 */
class SubmitController extends Controller
{
    use MediaFactory;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        parent::__construct();

        $this->user = $user;
    }

    /**
     * @param MicroblogRequest $request
     * @param Dispatcher $dispatcher
     * @param $microblog
     * @return MicroblogResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(MicroblogRequest $request, Dispatcher $dispatcher, ?Microblog $microblog)
    {
        $this->user->pushCriteria(new WithTrashed());

        if (!$microblog->exists) {
            $microblog->user()->associate($this->auth);
        } else {
            $this->authorize('update', $microblog);
        }

        $microblog->fill($request->only(['text']));

        $this->transaction(function () use ($microblog, $request) {
            $microblog->save();
            $microblog->saveMedia($request->input('media'));

            $object = (new Stream_Microblog())->map($microblog);

            if ($microblog->wasRecentlyCreated) {
                // increase reputation points
                app('reputation.microblog.create')->map($microblog)->save();

                // put this to activity stream
                stream(Stream_Create::class, $object);

                if ($this->auth->allow_subscribe) {
                    // enable subscribe button
                    $microblog->is_subscribed = true;
                    $microblog->subscribers()->create(['user_id' => $this->auth->id]);
                }
            } else {
                stream(Stream_Update::class, $object);
            }

            $helper = new HashHelper();
            $microblog->setTags($helper->grab($microblog->html));
        });

        broadcast(new MicroblogSaved($microblog))->toOthers();

        MicroblogResource::withoutWrapping();

        return new MicroblogResource($microblog);
    }

    /**
     * Usuniecie wpisu z mikrobloga
     *
     * @param \Coyote\Microblog $microblog
     */
    public function delete($microblog)
    {
        abort_if(!$microblog->exists, 404);
        $this->authorize('delete', $microblog);

        $this->transaction(function () use ($microblog) {
            $microblog->delete();
            // cofniecie pkt reputacji
            app('reputation.microblog.create')->undo($microblog->id);

            // put this to activity stream
            stream(Stream_Delete::class, (new Stream_Microblog())->map($microblog));
        });

        event(new MicroblogWasDeleted($microblog));
    }
}
