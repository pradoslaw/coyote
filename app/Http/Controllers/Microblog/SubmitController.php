<?php
namespace Coyote\Http\Controllers\Microblog;

use Coyote\Events\MicroblogDeleted;
use Coyote\Events\MicroblogSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\MicroblogRequest;
use Coyote\Http\Resources\MicroblogResource;
use Coyote\Microblog;
use Coyote\Notifications\Microblog\DeletedNotification;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Activities\Restore as Stream_Restore;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Microblog as Stream_Microblog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubmitController extends Controller
{
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
     * @param Microblog $microblog
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(MicroblogRequest $request, Microblog $microblog)
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
            $microblog->assets()->sync($request->input('assets'));

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

            $microblog->setTags(array_pluck($request->input('tags', []), 'name'));
        });

        broadcast(new MicroblogSaved($microblog))->toOthers();

        MicroblogResource::withoutWrapping();

        $microblog->unsetRelation('assets');
        $microblog->load(['assets', 'tags']);

        return (new MicroblogResource($microblog))->response($this->request)->setStatusCode($microblog->wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK);
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

        if ($this->userId !== $microblog->user_id) {
            $microblog->user->notify(new DeletedNotification($microblog, $this->auth));
        }

        event(new MicroblogDeleted($microblog));
    }

    public function restore(int $id)
    {
        $microblog = Microblog::withTrashed()->findOrFail($id);

        $this->authorize('delete', $microblog);

        $this->transaction(function () use ($microblog) {
            $microblog->restore();

            // put this to activity stream
            stream(Stream_Restore::class, (new Stream_Microblog())->map($microblog));
        });

        event(new MicroblogSaved($microblog));

        if ($microblog->parent_id) {
            event(new MicroblogSaved($microblog->parent));
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        return response($this->getParser()->parse((string)$request->get('text')));
    }

    /**
     * @param Microblog $microblog
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function toggleSponsored(Microblog $microblog)
    {
        $this->authorize('moderate', $microblog);

        $microblog->is_sponsored = !$microblog->is_sponsored;
        $microblog->save();
    }

    /**
     * @return \Coyote\Services\Parser\Factories\PostFactory
     */
    private function getParser()
    {
        return app('parser.post');
    }
}
