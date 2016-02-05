<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Microblog\Subscriber;
use Coyote\Http\Controllers\Controller;
use Coyote\Parser\Reference\Login as Ref_Login;
use Coyote\Parser\Reference\Hash as Ref_Hash;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as Alert;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Activities\Update as Stream_Update;
use Coyote\Stream\Activities\Delete as Stream_Delete;
use Coyote\Stream\Objects\Microblog as Stream_Microblog;
use Coyote\Alert\Providers\Microblog\Login as Alert_Login;
use Illuminate\Http\Request;

/**
 * Class SubmitController
 * @package Coyote\Http\Controllers\Microblog
 */
class SubmitController extends Controller
{
    /**
     * @var Microblog
     */
    private $microblog;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Alert
     */
    private $alert;

    /**
     * Nie musze tutaj wywolywac konstruktora klasy macierzystej. Nie potrzeba...
     *
     * @param Microblog $microblog
     * @param User $user
     * @param Alert $alert
     */
    public function __construct(Microblog $microblog, User $user, Alert $alert)
    {
        $this->microblog = $microblog;
        $this->user = $user;
        $this->alert = $alert;
    }

    /**
     * Publikowanie wpisu na mikroblogu
     *
     * @param null|int $id
     * @return $this
     */
    public function save($id = null)
    {
        $this->validate(request(), [
            'text'          => 'required|string'
        ]);

        $microblog = $this->microblog->findOrNew($id);
        $data = request()->only(['text']);

        if ($id === null) {
            $user = auth()->user();
            $data['user_id'] = $user->id;

            $media = ['image' => []];
        } else {
            $this->authorize('update', $microblog);

            $user = $this->user->find($microblog->user_id, ['id', 'name', 'is_blocked', 'is_active', 'photo']);
            $media = $microblog->media;

            if (empty($media['image'])) {
                $media = ['image' => []];
            }
        }

        if (request()->has('thumbnail') || count($media['image']) > 0) {
            $delete = array_diff($media['image'], (array) request()->get('thumbnail'));

            foreach ($delete as $name) {
                unlink(public_path('storage/microblog/' . $name));
                unset($media['image'][array_search($name, $media['image'])]);
            }

            $insert = array_diff((array) request()->get('thumbnail'), $media['image']);

            foreach ($insert as $name) {
                rename(public_path('storage/tmp/' . $name), public_path('storage/microblog/' . $name));
                $media['image'][] = $name;
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

                if ($usersId) {
                    (new Alert_Login($this->alert))->with([
                        'users_id'    => $usersId,
                        'sender_id'   => $user->id,
                        'sender_name' => $user->name,
                        'subject'     => excerpt($microblog->text, 48),
                        'url'         => route('microblog.view', [$microblog->id], false)
                    ])->notify();
                }

                if (auth()->user()->allow_subscribe) {
                    // enable subscribe button
                    $microblog->subscribe_on = true;
                    Subscriber::insert(['microblog_id' => $microblog->id, 'user_id' => $user->id]);
                }
            } else {
                stream(Stream_Update::class, $object);
            }

            $ref = new Ref_Hash();
            $this->microblog->setTags($microblog->id, $ref->grab($microblog->text));
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
     * @return $this
     */
    public function edit($id)
    {
        $microblog = $this->microblog->findOrFail($id);
        $this->authorize('update', $microblog);

        $thumbnails = [];

        if (isset($microblog->media['image'])) {
            foreach ($microblog->media['image'] as $name) {
                $thumbnails[$name] = url('storage/microblog/' . $name);
            }
        }

        return view('microblog.edit', ['thumbnails' => $thumbnails])->with($microblog->toArray());
    }

    /**
     * Return small piece of code (thumbnail container)
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
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

        if ($request->file('photo')->isValid()) {
            $fileName = uniqid() . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move(public_path() . '/storage/tmp/', $fileName);

            return response()->json([
                'url' => url('storage/tmp/' . $fileName),
                'name' => $fileName
            ]);
        }
    }

    /**
     * Paste image from clipboard
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paste()
    {
        $input = file_get_contents("php://input");

        if (strlen($input) > (config('filesystems.upload_max_size') * 1024 * 1024)) {
            abort(500, 'File is too big');
        }

        $fileName = uniqid() . '.png';
        $path = public_path('storage/tmp/' . $fileName);

        file_put_contents($path, file_get_contents('data://' . substr($input, 7)));

        return response()->json([
            'name' => $fileName,
            'url' => url('storage/tmp/' . $fileName)
        ]);
    }
}
