<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Events\MicroblogWasDeleted;
use Coyote\Events\MicroblogWasSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\FilesystemFactory;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Services\Parser\Helpers\Login as LoginHelper;
use Coyote\Services\Parser\Helpers\Hash as HashHelper;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Microblog as Stream_Microblog;
use Illuminate\Http\Request;

/**
 * Class SubmitController
 * @package Coyote\Http\Controllers\Microblog
 */
class SubmitController extends Controller
{
    use FilesystemFactory, MediaFactory;

    /**
     * @var Microblog
     */
    private $microblog;

    /**
     * @var User
     */
    private $user;

    /**
     * Nie musze tutaj wywolywac konstruktora klasy macierzystej. Nie potrzeba...
     *
     * @param Microblog $microblog
     * @param User $user
     */
    public function __construct(Microblog $microblog, User $user)
    {
        $this->microblog = $microblog;
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

        if (empty($microblog->id)) {
            $user = auth()->user();
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

        $isExist = $microblog->exists;
        $microblog->fill($data);

        $this->transaction(function () use (&$microblog, $user, $isExist) {
            $microblog->save();

            // parsing text and store it in cache
            $microblog->text = app('parser.microblog')->parse($microblog->text);

            $object = (new Stream_Microblog())->map($microblog);

            if (!$isExist) {
                // increase reputation points
                app('Reputation\Microblog\Create')->map($microblog)->save();

                // put this to activity stream
                stream(Stream_Create::class, $object);

                $helper = new LoginHelper();
                // get id of users that were mentioned in the text
                $usersId = $helper->grab($microblog->text);

                if (!empty($usersId)) {
                    app('alert.microblog.login')->with([
                        'users_id'    => $usersId,
                        'sender_id'   => $user->id,
                        'sender_name' => $user->name,
                        'subject'     => excerpt($microblog->text),
                        'url'         => route('microblog.view', [$microblog->id], false)
                    ])->notify();
                }

                if (auth()->user()->allow_subscribe) {
                    // enable subscribe button
                    $microblog->subscribe_on = true;
                    $microblog->subscribers()->create(['user_id' => $user->id]);
                }
            } else {
                stream(Stream_Update::class, $object);
            }

            $helper = new HashHelper();
            $microblog->setTags($helper->grab($microblog->text));

            event(new MicroblogWasSaved($microblog));
        });

        // do przekazania do widoku...
        foreach (['name', 'is_blocked', 'is_active', 'photo'] as $key) {
            $microblog->$key = $user->$key;
        }

        return view($isExist ? 'microblog.text' : 'microblog.microblog')->with('microblog', $microblog);
    }

    /**
     * Edycja wpisu na mikroblogu. Odeslanie formularza zawierajacego tresc + zalaczniki
     *
     * @param \Coyote\Microblog $microblog
     * @return \Illuminate\View\View
     */
    public function edit($microblog)
    {
        $this->authorize('update', $microblog);

        return view('microblog.edit')->with('microblog', $microblog);
    }

    /**
     * Return small piece of code (thumbnail container)
     *
     * @return \Illuminate\Contracts\View\Factory
     */
    public function thumbnail()
    {
        return view('microblog.thumbnail');
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
            app('Reputation\Microblog\Create')->undo($microblog->id);
            $microblog->media = null; // MUST remove closure before serializing object

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
