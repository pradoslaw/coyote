<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface as Reputation;
use Coyote\Reputation\Microblog\Create as ReputationCreate;
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
     * @var Reputation
     */
    private $reputation;

    /**
     * Nie musze tutaj wywolywac konstruktora klasy macierzystej. Nie potrzeba...
     *
     * @param Microblog $microblog
     * @param User $user
     * @param Reputation $reputation
     */
    public function __construct(Microblog $microblog, User $user, Reputation $reputation)
    {
        $this->microblog = $microblog;
        $this->user = $user;
        $this->reputation = $reputation;
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

        \DB::transaction(function () use ($microblog, $id, $user) {
            $microblog->save();

            if (!$id) {
                $reputation = [
                    'microblogId'   => $microblog->id,
                    'url'           => route('microblog.view', [$microblog->id]),
                    'userId'        => $user->id,
                    'excerpt'       => str_limit($microblog->text, 250)
                ];

                // zwiekszenie pkt reputacji
                (new ReputationCreate($this->reputation))->save($reputation);
            }
        });

        // jezeli wpis zawiera jakies zdjecia, generujemy linki do miniatur
        // metoda thumbnails() przyjmuje w parametrze tablice tablic (wpisow, tj. mikroblogow)
        // stad takie zapis:
        $microblog = $this->microblog->thumbnails($microblog);

        // do przekazania do widoku...
        foreach (['name', 'is_blocked', 'is_active', 'photo'] as $key) {
            $microblog->$key = $user->$key;
        }

        return view($id ? 'microblog._microblog_text' : 'microblog._microblog')->with('microblog', $microblog);
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

        return view('microblog._edit', ['thumbnails' => $thumbnails])->with($microblog->toArray());
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
            (new ReputationCreate($this->reputation))->undo($microblog->id);
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
            'photo'             => 'required|image'
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
}
