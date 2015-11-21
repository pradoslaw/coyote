<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Eloquent\MicroblogRepository as Microblog;
use Coyote\Repositories\Eloquent\UserRepository as User;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    private $microblog;
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
     * Publikowanie komentarza na mikroblogu
     *
     * @param null|int $id
     * @return $this
     */
    public function save($id = null)
    {
        $this->validate(request(), [
            'text'          => 'required|string',
            'parent_id'     => 'sometimes|integer|exists:microblogs,id'
        ]);

        $microblog = $this->microblog->firstOrNew(['id' => $id]);

        if ($id === null) {
            $user = auth()->user();
            $data = request()->only(['text']) + ['user_id' => $user->id];
        } else {
            $user = $this->user->find($microblog->user_id, ['id', 'name', 'is_blocked', 'is_active', 'photo']);
            $data = request()->only(['text', 'parent_id']);
        }

        $microblog->fill($data);
        $microblog->save();

        return view('microblog._comment')->with('comment', array_merge($microblog->toArray(), [
            'user_id'               => $user->id,
            'name'                  => $user->name,
            'is_blocked'            => $user->is_blocked,
            'is_active'             => $user->is_active,
            'photo'                 => $user->photo
        ]));
    }

    /**
     * Edycja komentarza na mikroblogu.
     *
     * @param int $id
     * @return string
     */
    public function edit($id)
    {
        $microblog = $this->microblog->findOrFail($id);
        return response($microblog->text);
    }

    /**
     * Usuniecie komentarza z mikrobloga
     *
     * @param int $id
     */
    public function delete($id)
    {
        $microblog = $this->microblog->findOrFail($id);
        $microblog->delete();
    }
}
