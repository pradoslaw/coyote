<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Alert\Alert;
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
use Coyote\Parser\Reference\Login as Ref_Login;
use Gate;

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

        list($post, $topic, $forum) = $this->checkAbility($request->get('post_id'));

        $comment = $this->comment->findOrNew($id);
        $target = (new Stream_Topic())->map($topic, $forum);

        if ($id === null) {
            $user = auth()->user();
            $data = $request->only(['text']) + ['user_id' => $user->id, 'post_id' => $request->get('post_id')];

            $activity = Stream_Create::class;
        } else {
            $this->authorize('update', [$comment, $forum]);

            $user = $this->user->find($comment->user_id, ['id', 'name', 'is_blocked', 'is_active', 'photo']);
            $data = $request->only(['text']);

            $activity = Stream_Update::class;
        }

        $comment->fill($data);

        \DB::transaction(function () use ($comment, $id, $post, $topic, $forum, $activity, $target) {
            $comment->save();

            // we need to parse text first (and store it in cache)
            $parser = app()->make('Parser\Comment');
            $comment->text = $parser->parse($comment->text);

            // it is IMPORTANT to parse text first, and then put information to activity stream.
            // so that we will save plan text (without markdown)
            $object = (new Stream_Comment())->map($post, $comment, $forum, $topic);
            stream($activity, $object, $target);

            if (!$id) {
                $alert = new Alert();
                $notification = [
                    'sender_id'   => $this->userId,
                    'sender_name' => auth()->user()->name,
                    'subject'     => excerpt($topic->subject),
                    'excerpt'     => excerpt($comment->text),
                    'url'         => $object->url
                ];

                $subscribersId = $forum->onlyUsersWithAccess($post->subscribers()->lists('user_id')->toArray());

                if ($subscribersId) {
                    $alert->attach(
                        // $subscribersId can be int or array. we need to cast to array type
                        app()->make('Alert\Post\Subscriber')->with($notification)->setUsersId($subscribersId)
                    );
                }

                // get id of users that were mentioned in the text
                $subscribersId = $forum->onlyUsersWithAccess((new Ref_Login())->grab($comment->text));

                if ($subscribersId) {
                    $alert->attach(
                        app()->make('Alert\Post\Comment\Login')->with($notification)->setUsersId($subscribersId)
                    );
                }

                $alert->notify();

                // subscribe post. notify about all future comments to this post
                $post->subscribe($this->userId, true);
            }
        });

        foreach (['name', 'is_blocked', 'is_active', 'photo'] as $key) {
            $comment->$key = $user->$key;
        }

        // we need to pass is_writeable variable to let know that we are able to edit/delete this comment
        return view('forum.partials.comment', ['is_writeable' => true])->with(compact('comment', 'forum'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit($id)
    {
        $comment = $this->comment->findOrFail($id);
        list(, , $forum) = $this->checkAbility($comment->post_id);

        $this->authorize('update', [$comment, $forum]);

        return view('forum.partials.form', ['post' => ['id' => $comment->post_id]])->with('comment', $comment);
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function delete($id)
    {
        $comment = $this->comment->findOrFail($id);
        list($post, $topic, $forum) = $this->checkAbility($comment->post_id);

        $this->authorize('delete', [$comment, $forum]);

        $target = (new Stream_Topic())->map($topic, $forum);
        $object = (new Stream_Comment())->map($post, $comment, $forum, $topic);

        \DB::transaction(function () use ($comment, $object, $target) {
            $comment->delete();

            stream(Stream_Delete::class, $object, $target);
        });
    }

    private function checkAbility($postId)
    {
        $post = $this->post->findOrFail($postId, ['id', 'topic_id', 'forum_id']);
        $forum = $this->forum->findOrFail($post->forum_id);

        // Maybe user does not have an access to this category?
        if (!$forum->userCanAccess($this->userId)) {
            abort(401, 'Unauthorized');
        }

        $topic = $this->topic->findOrFail($post->topic_id, ['id', 'forum_id', 'path', 'subject', 'is_locked']);

        // Only moderators can delete this post if topic (or forum) was locked
        if (Gate::denies('delete', $forum)) {
            if ($topic->is_locked || $forum->is_locked || $post->deleted_at) {
                abort(401, 'Unauthorized');
            }
        }

        return [$post, $topic, $forum];
    }
}
