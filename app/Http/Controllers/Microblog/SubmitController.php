<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Events\MicroblogWasDeleted;
use Coyote\Events\MicroblogSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Http\Requests\MicroblogRequest;
use Coyote\Http\Resources\MicroblogResource;
use Coyote\Microblog;
use Coyote\Notifications\Microblog\UserMentionedNotification;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Services\Media\Clipboard;
use Coyote\Services\Parser\Helpers\Login as LoginHelper;
use Coyote\Services\Parser\Helpers\Hash as HashHelper;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Microblog as Stream_Microblog;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Http\Request;

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

        $microblog->fill($request->only(['text', 'media']));

        $this->transaction(function () use ($microblog) {
            $microblog->save();

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

        $helper = new LoginHelper();
        // get id of users that were mentioned in the text
        $usersId = $helper->grab($microblog->html);

        if (!empty($usersId)) {
            $dispatcher->send(
                $this->user->findMany($usersId)->exceptUser($this->auth),
                new UserMentionedNotification($microblog)
            );
        }

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

            event(new MicroblogWasDeleted($microblog));
            // put this to activity stream
            stream(Stream_Delete::class, (new Stream_Microblog())->map($microblog));
        });
    }

    /**
     * Upload pliku na serwer wraz z wczesniejsza walidacja
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $this->validate($request, [
            'photo'             => 'required|mimes:jpeg,jpg,png,gif|max:' . (config('filesystems.upload_max_size') * 1024)
        ]);

        $media = $this->getMediaFactory()->make('attachment')->upload($request->file('photo'));

        return response()->json([
            'url' => (string) $media->url(),
            'thumbnail' => $media->url()->thumbnail('microblog'),
            'name' => $media->getFilename()
        ]);
    }

    /**
     * Paste image from clipboard
     *
     * @param Clipboard $clipboard
     * @return \Illuminate\Http\JsonResponse
     */
    public function paste(Clipboard $clipboard)
    {
        $media = $clipboard->paste('attachment');

        return response()->json([
            'name' => $media->getFilename(),
            'url' => (string) $media->url(),
            'thumbnail' => $media->url()->thumbnail('microblog')
        ]);
    }
}
