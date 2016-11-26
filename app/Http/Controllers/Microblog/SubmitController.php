<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Events\MicroblogWasDeleted;
use Coyote\Events\MicroblogWasSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\FilesystemFactory;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Services\Parser\Helpers\Login as LoginHelper;
use Coyote\Services\Parser\Helpers\Hash as HashHelper;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Microblog as Stream_Microblog;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Http\Request;

/**
 * Class SubmitController
 * @package Coyote\Http\Controllers\Microblog
 */
class SubmitController extends Controller
{
    use FilesystemFactory, MediaFactory;

    /**
     * @var User
     */
    private $user;

    /**
     * Nie musze tutaj wywolywac konstruktora klasy macierzystej. Nie potrzeba...
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Publikowanie wpisu na mikroblogu
     *
     * @param Request $request
     * @param \Coyote\Microblog $microblog
     * @return \Illuminate\View\View
     */
    public function save(Request $request, $microblog)
    {
        $this->validate($request, [
            'text'          => 'required|string|max:10000|throttle:' . $microblog->id
        ]);

        $data = $request->only(['text']);

        if (!$microblog->exists) {
            $user = $this->auth;
            $data['user_id'] = $user->id;
        } else {
            $this->authorize('update', $microblog);

            $user = $this->user->find($microblog->user_id, ['id', 'name', 'is_blocked', 'is_active', 'photo']);
        }

        if ($request->has('thumbnail') || count($microblog->media) > 0) {
            /** @var \Coyote\Services\Media\MediaInterface $media */
            foreach ($microblog->media as $media) {
                if (!in_array($media->getFilename(), $request->get('thumbnail', []))) {
                    $media->delete();
                }
            }

            $microblog->media = $request->get('thumbnail');
        }

        $microblog->fill($data);

        $this->transaction(function () use (&$microblog, $user) {
            $microblog->save();
            $object = (new Stream_Microblog())->map($microblog);

            if ($microblog->wasRecentlyCreated) {
                // increase reputation points
                app('reputation.microblog.create')->map($microblog)->save();

                // put this to activity stream
                stream(Stream_Create::class, $object);

                $helper = new LoginHelper();
                // get id of users that were mentioned in the text
                $usersId = $helper->grab($microblog->html);

                if (!empty($usersId)) {
                    app('alert.microblog.login')->with([
                        'users_id'    => $usersId,
                        'sender_id'   => $user->id,
                        'sender_name' => $user->name,
                        'subject'     => excerpt($microblog->html),
                        'url'         => UrlBuilder::microblog($microblog)
                    ])->notify();
                }

                if ($this->auth->allow_subscribe) {
                    // enable subscribe button
                    $microblog->subscribe_on = true;
                    $microblog->subscribers()->create(['user_id' => $user->id]);
                }
            } else {
                stream(Stream_Update::class, $object);
            }

            $helper = new HashHelper();
            $microblog->setTags($helper->grab($microblog->html));

            event(new MicroblogWasSaved($microblog));
        });

        // do przekazania do widoku...
        foreach (['name', 'is_blocked', 'is_active', 'photo'] as $key) {
            $microblog->{$key} = $user->{$key};
        }

        // passing html version of the entry...
        $microblog->text = $microblog->html;

        return view(!$microblog->wasRecentlyCreated ? 'microblog.partials.text' : 'microblog.partials.microblog')->with('microblog', $microblog);
    }

    /**
     * Edycja wpisu na mikroblogu. Odeslanie formularza zawierajacego tresc + zalaczniki
     *
     * @param \Coyote\Microblog $microblog
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function edit($microblog)
    {
        $this->authorize('update', $microblog);

        return view('microblog.partials.edit')->with('microblog', $microblog);
    }

    /**
     * Return small piece of code (thumbnail container)
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function thumbnail()
    {
        return view('microblog.partials.thumbnail');
    }

    /**
     * Usuniecie wpisu z mikrobloga
     *
     * @param \Coyote\Microblog $microblog
     */
    public function delete($microblog)
    {
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
            'photo'             => 'required|image|max:' . (config('filesystems.upload_max_size') * 1024)
        ]);

        $media = $this->getMediaFactory('attachment')->upload($request->file('photo'));

        return response()->json([
            'url' => $media->url(),
            'name' => $media->getFilename()
        ]);
    }

    /**
     * Paste image from clipboard
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paste()
    {
        $input = file_get_contents("php://input");

        $validator = $this->getValidationFactory()->make(
            ['length' => strlen($input)],
            ['length' => 'max:' . config('filesystems.upload_max_size') * 1024 * 1024]
        );

        $this->validateWith($validator);
        $media = $this->getMediaFactory('attachment')->put(file_get_contents('data://' . substr($input, 7)));

        return response()->json([
            'name' => $media->getFilename(),
            'url' => $media->url()
        ]);
    }
}
