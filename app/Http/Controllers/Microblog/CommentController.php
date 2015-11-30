<?php

namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\Controller;
use Coyote\Parser\Reference\Login as Ref_Login;
use Coyote\Parser\Reference\Hash as Ref_Hash;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface as Microblog;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as Stream;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as Alert;
use Coyote\Alert\Alert as Alerts;
use Coyote\Alert\Providers\Microblog\Watch as Alert_Watch;
use Coyote\Alert\Providers\Microblog\Login as Alert_Login;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Activities\Update as Stream_Update;
use Coyote\Stream\Activities\Delete as Stream_Delete;
use Coyote\Stream\Objects\Microblog as Stream_Microblog;
use Coyote\Stream\Objects\Comment as Stream_Comment;
use Coyote\Stream\Actor as Stream_Actor;
use Coyote\Stream\Stream as Stream_Activity;

class CommentController extends Controller
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
     * @var Stream
     */
    private $stream;

    /**
     * Nie musze tutaj wywolywac konstruktora klasy macierzystej. Nie potrzeba...
     *
     * @param Microblog $microblog
     * @param User $user
     * @param Alert $alert
     * @param Stream $stream
     */
    public function __construct(Microblog $microblog, User $user, Alert $alert, Stream $stream)
    {
        $this->microblog = $microblog;
        $this->user = $user;
        $this->alert = $alert;
        $this->stream = $stream;
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

        $microblog = $this->microblog->findOrNew($id);

        if ($id === null) {
            $user = auth()->user();
            $data = request()->only(['text', 'parent_id']) + ['user_id' => $user->id];
        } else {
            $this->authorize('update', $microblog);

            $user = $this->user->find($microblog->user_id, ['id', 'name', 'is_blocked', 'is_active', 'photo']);
            $data = request()->only(['text']);
        }

        $microblog->fill($data);

        $parent = $this->microblog->find($microblog->parent_id);

        $object = new Stream_Comment();
        $target = (new Stream_Microblog())->map($parent);
        $actor = new Stream_Actor($user);
        $alert = new Alert_Watch($this->alert);
        $stream = new Stream_Activity($this->stream);

        if (!$id) {
            $activity = new Stream_Create($actor, $object, $target);
        } else {
            $activity = new Stream_Update($actor, $object, $target);
        }

        \DB::transaction(function () use ($id, &$microblog, $activity, $alert, $stream, $object, $user, $parent) {
            $microblog->save();

            // we need to parse text first (and store it in cache)
            $parser = app()->make('Parser\Comment');
            $microblog->text = $parser->parse($microblog->text);

            if (!$id) {
                $watchers = $this->microblog->getWatchers($microblog->parent_id);
                $alert = new Alerts();

                // we need to send alerts AFTER saving comment to database because we need ID of comment
                $alertData = [
                    'microblog_id'=> $microblog->id,
                    'content'     => $microblog->text,
                    'excerpt'     => excerpt($microblog->text),
                    'sender_id'   => $user->id,
                    'sender_name' => $user->name,
                    'subject'     => excerpt($parent->text, 48), // original exerpt of parent entry
                    'url'         => route('microblog.view', [$parent->id], false) . '#comment-' . $microblog->id
                ];

                if ($watchers) {
                    // new comment. should we send a notification?
                    $alert->attach((new Alert_Watch($this->alert, $alertData))->setUsersId($watchers));
                }

                $ref = new Ref_Login();
                // get id of users that were mentioned in the text
                $usersId = $ref->grab($microblog->text);

                if ($usersId) {
                    $alert->attach((new Alert_Login($this->alert, $alertData))->setUsersId($usersId));
                }

                // send a notify
                $alert->notify();
            }

            $ref = new Ref_Hash();
            $this->microblog->setTags($microblog->id, $ref->grab($microblog->text));

            // map microblog object into stream activity object
            $object->map($microblog);

            // put item into stream activity
            $stream->add($activity);
        });

        foreach (['name', 'is_blocked', 'is_active', 'photo'] as $key) {
            $microblog->$key = $user->$key;
        }

        return view('microblog._comment')->with('comment', $microblog)->with('microblog', ['id' => $microblog->parent_id]);
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
        $this->authorize('update', $microblog);

        return response($microblog->text);
    }

    /**
     * Usuniecie komentarza z mikrobloga
     *
     * @param int $id
     */
    public function delete($id)
    {
        $microblog = $this->microblog->findOrFail($id, ['id', 'user_id']);
        $this->authorize('delete', $microblog);

        \DB::transaction(function () use ($microblog) {
            $microblog->delete();

            $parent = $this->microblog->find($microblog->parent_id);
            $object = (new Stream_Comment())->map($microblog);
            $target = (new Stream_Microblog())->map($parent);

            $delete = new Stream_Delete(new Stream_Actor(auth()->user()), $object, $target);
            (new Stream_Activity($this->stream))->add($delete);
        });
    }

    /**
     * Pokaz pozostale komentarze do wpisu
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $parser = app()->make('Parser\Comment');
        $comments = $this->microblog->getComments([$id])->slice(0, -2);

        foreach ($comments as &$comment) {
            $comment->text = $parser->parse($comment->text);
        }
        return view('microblog._comments', ['id' => $id, 'comments' => $comments]);
    }
}
