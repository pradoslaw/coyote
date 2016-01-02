<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\Post\CommentRepositoryInterface as Comment;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Stream\Activities\Create as Stream_Create;
use Coyote\Stream\Activities\Update as Stream_Update;
use Coyote\Stream\Activities\Delete as Stream_Delete;
use Coyote\Stream\Objects\Comment as Stream_Comment;
use Coyote\Stream\Objects\Topic as Stream_Topic;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * @var Comment
     */
    private $comment;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Topic
     */
    private $topic;

    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var Post
     */
    private $post;

    /**
     * @param Comment $comment
     * @param User $user
     * @param Topic $topic
     * @param Forum $forum
     * @param Post $post
     */
    public function __construct(Comment $comment, User $user, Topic $topic, Forum $forum, Post $post)
    {
        parent::__construct();

        $this->comment = $comment;
        $this->user = $user;
        $this->topic = $topic;
        $this->forum = $forum;
        $this->post = $post;
    }

    /**
     * @param Request $request
     * @param null $id
     * @return $this
     */
    public function save(Request $request, $id = null)
    {
        $this->validate(request(), [
            'text'          => 'required|string|max:580',
            'post_id'       => 'required|integer|exists:posts,id'
        ]);

        $post = $this->post->findOrFail($request->get('post_id'), ['id', 'topic_id']);
        $topic = $this->topic->findOrFail($post->topic_id, ['id', 'forum_id', 'path', 'subject']);
        $forum = $this->forum->findOrFail($topic->forum_id);

        $comment = $this->comment->findOrNew($id);
        $target = (new Stream_Topic())->map($topic, $forum);

        if ($id === null) {
            $user = auth()->user();
            $data = $request->only(['text']) + ['user_id' => $user->id, 'post_id' => $request->get('post_id')];

            $activity = Stream_Create::class;
        } else {
            $this->authorize('update', $comment);

            $user = $this->user->find($comment->user_id, ['id', 'name', 'is_blocked', 'is_active', 'photo']);
            $data = $request->only(['text']);

            $activity = Stream_Update::class;
        }

        $comment->fill($data);

        \DB::transaction(function () use ($comment, $id, $post, $topic, $forum, $activity, $target) {
            $comment->save();

            $object = (new Stream_Comment())->map($post, $comment, $forum, $topic);
            stream($activity, $object, $target);

            // we need to parse text first (and store it in cache)
            $parser = app()->make('Parser\Comment');
            $comment->text = $parser->parse($comment->text);
        });

        foreach (['name', 'is_blocked', 'is_active', 'photo'] as $key) {
            $comment->$key = $user->$key;
        }

        return view('forum.comment')->with('comment', $comment);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit($id)
    {
        $comment = $this->comment->findOrFail($id);
        $this->authorize('update', $comment);

        return response($comment->text);
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function delete($id)
    {
        $comment = $this->comment->findOrFail($id, ['id', 'user_id', 'post_id']);
        $this->authorize('delete', $comment);

        $post = $this->post->findOrFail($comment->post_id, ['id', 'topic_id']);
        $topic = $this->topic->findOrFail($post->topic_id, ['id', 'forum_id', 'path', 'subject']);
        $forum = $this->forum->findOrFail($topic->forum_id);

        $target = (new Stream_Topic())->map($topic, $forum);
        $object = (new Stream_Comment())->map($post, $comment, $forum, $topic);

        \DB::transaction(function () use ($comment, $object, $target) {
            $comment->delete();

            stream(Stream_Delete::class, $object, $target);
        });
    }
}
