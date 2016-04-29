<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Events\MicroblogWasDeleted;
use Coyote\Events\MicroblogWasSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\FilesystemFactory;
use Coyote\Http\Factories\MediaFactory;
use Coyote\Services\Parser\Reference\Login as Ref_Login;
use Coyote\Services\Parser\Reference\Hash as Ref_Hash;
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
     * @param null|int $id
     * @return $this
     */
    public function save(Request $request, $id = null)
    {
        $this->validate($request, [
            'text'          => 'required|string|max:10000|throttle'
        ]);

        $microblog = $this->microblog->findOrNew($id);
        $data = $request->only(['text']);

        $media = [];

        if ($id === null) {
            $user = auth()->user();
            $data['user_id'] = $user->id;
        } else {
            $this->authorize('update', $microblog);

            $user = $this->user->find($microblog->user_id, ['id', 'name', 'is_blocked', 'is_active', 'photo']);

            if (!empty($microblog->media)) {
                /** @var \Coyote\Services\Media\MediaInterface $item */
                foreach ($microblog->media as $item) {
                    $media[] = $item->getFilename();
                }
            }
        }

        if ($request->has('thumbnail') || count($media) > 0) {
            $delete = array_diff($media, (array) $request->get('thumbnail'));
            $fs = $this->getFilesystemFactory();

            foreach ($delete as $name) {
                // @todo te metode nalezy przeniesc do serwisu Media
                $fs->delete('attachment/' . $name);
                unset($media[array_search($name, $media)]);
            }

            $insert = array_diff((array) $request->get('thumbnail'), $media);

            foreach ($insert as $name) {
                $media[] = $name;
            }

            $microblog->media = $media;
        }

        $microblog->fill($data);

        \DB::transaction(function () use (&$microblog, $id, $user) {
            $microblog->save();

            // parsing text and store it in cache
            $microblog->text = app()->make('Parser\Microblog')->parse($microblog->text);

            $object = (new Stream_Microblog())->map($microblog);

            if (!$id) {
                // increase reputation points
                app()->make('Reputation\Microblog\Create')->map($microblog)->save();

                // put this to activity stream
                stream(Stream_Create::class, $object);

                $ref = new Ref_Login();
                // get id of users that were mentioned in the text
                $usersId = $ref->grab($microblog->text);

                if (!empty($usersId)) {
                    app()->make('Alert\Microblog\Login')->with([
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

            $ref = new Ref_Hash();
            $microblog->setTags($ref->grab($microblog->text));

            event(new MicroblogWasSaved($microblog));
        });

        // jezeli wpis zawiera jakies zdjecia, generujemy linki do miniatur
        // metoda thumbnails() przyjmuje w parametrze tablice tablic (wpisow, tj. mikroblogow)
        // stad takie zapis:
        $microblog = $this->microblog->thumbnails($microblog);

        // do przekazania do widoku...
        foreach (['name', 'is_blocked', 'is_active', 'photo'] as $key) {
            $microblog->$key = $user->$key;
        }

        return view($id ? 'microblog.text' : 'microblog.microblog')->with('microblog', $microblog);
    }

    /**
     * Edycja wpisu na mikroblogu. Odeslanie formularza zawierajacego tresc + zalaczniki
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $microblog = $this->microblog->findOrFail($id);
        $this->authorize('update', $microblog);

        $thumbnails = [];

        if (!empty($microblog->media)) {
            /** @var \Coyote\Services\Media\MediaInterface $media */
            foreach ($microblog->media as $media) {
                $thumbnails[$media->getFilename()] = $media->url();
            }
        }

        return view('microblog.edit', ['thumbnails' => $thumbnails])->with($microblog->toArray());
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
     * @param $id
     */
    public function delete($id)
    {
        $microblog = $this->microblog->findOrFail($id, ['id', 'user_id']);
        $this->authorize('delete', $microblog);

        \DB::transaction(function () use ($microblog) {
            $microblog->delete();
            // cofniecie pkt reputacji
            app()->make('Reputation\Microblog\Create')->undo($microblog->id);

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

        $fileName = uniqid() . '.png';
        $path = 'tmp/' . $fileName;

        $this->getFilesystemFactory()->put($path, file_get_contents('data://' . substr($input, 7)));

        return response()->json([
            'name' => $fileName,
            'url' => asset('storage/' . $path)
        ]);
    }
}
