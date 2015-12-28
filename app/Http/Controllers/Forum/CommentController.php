<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\Post\CommentRepositoryInterface as Comment;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    private $comment;
    private $user;

    public function __construct(Comment $comment, User $user)
    {
        parent::__construct();

        $this->comment = $comment;
        $this->user = $user;
    }

    public function save(Request $request, $id = null)
    {
        $this->validate(request(), [
            'text'          => 'required|string|max:580',
            'post_id'       => 'required|integer|exists:posts,id'
        ]);

        $comment = $this->comment->findOrNew($id);

        if ($id === null) {
            $user = auth()->user();
            $data = $request->only(['text']) + ['user_id' => $user->id, 'post_id' => $request->get('post_id')];
        } else {
            $this->authorize('update', $comment);

            $user = $this->user->find($comment->user_id, ['id', 'name', 'is_blocked', 'is_active', 'photo']);
            $data = $request->only(['text']);
        }

        $comment->fill($data);

        \DB::transaction(function () use ($comment) {
            $comment->save();

            // we need to parse text first (and store it in cache)
            $parser = app()->make('Parser\Comment');
            $comment->text = $parser->parse($comment->text);
        });

        foreach (['name', 'is_blocked', 'is_active', 'photo'] as $key) {
            $comment->$key = $user->$key;
        }

        return view('forum.comment')->with('comment', $comment);
    }

    public function edit($id)
    {
        //
    }
}
