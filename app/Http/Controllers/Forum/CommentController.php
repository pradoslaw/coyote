<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Events\CommentDeleted;
use Coyote\Events\CommentSaved;
use Coyote\Notifications\Post\Comment\UserMentionedNotification;
use Coyote\Notifications\Post\CommentedNotification;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Http\Controllers\Controller;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Comment as Stream_Comment;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Http\Request;
use Coyote\Services\Parser\Helpers\Login as LoginHelper;

class CommentController extends Controller
{
    /**
     * @var \Coyote\Post\Comment
     */
    private $comment;

    /**
     * @var \Coyote\Topic
     */
    private $topic;

    /**
     * @var \Coyote\Forum
     */
    private $forum;

    /**
     * @var \Coyote\Post
     */
    private $post;

    public function __construct()
    {
        parent::__construct();

        $this->middleware(function (Request $request, $next) {
            // set variables from middleware
            foreach ($request->attributes->keys() as $key) {
                $this->{$key} = $request->attributes->get($key);
            }

            return $next($request);
        });
    }

    /**
     * @param Request $request
     * @param Dispatcher $dispatcher
     * @param null|int $id
     * @return $this
     */
    public function save(Request $request, Dispatcher $dispatcher, $id = null)
    {
        $this->validate($request, [
            'text'          => 'required|string|max:580',
            'post_id'       => 'required|integer|exists:posts,id'
        ]);

        $target = (new Stream_Topic())->map($this->topic);

        if ($id === null) {
            $user = $this->auth;
            $data = $request->only(['text']) + ['user_id' => $user->id, 'post_id' => $request->input('post_id')];

            $activity = Stream_Create::class;
        } else {
            $this->authorize('update', [$this->comment, $this->forum]);

            $user = $this->comment->user()->withTrashed()->first(['id', 'name', 'is_blocked', 'deleted_at']);
            $data = $request->only(['text']);

            $activity = Stream_Update::class;
        }

        $this->comment->fill($data);

        $this->transaction(function () use ($activity, $target, $dispatcher) {
            $this->comment->save();

            // it is IMPORTANT to parse text first, and then put information to activity stream.
            // so that we will save plan text (without markdown)
            $object = (new Stream_Comment())->map($this->post, $this->comment, $this->topic);
            stream($activity, $object, $target);

            if ($this->comment->wasRecentlyCreated) {
                // subscribe post. notify about all future comments to this post
                $this->post->subscribe($this->userId, true);
            }
        });

        $subscribers = [];

        if ($this->comment->wasRecentlyCreated) {
            $subscribers = $this->post->subscribers()->with('user')->get()->pluck('user')->exceptUser($this->auth);

            $dispatcher->send(
                $subscribers,
                (new CommentedNotification($this->comment))
            );
        }

        $usersId = (new LoginHelper())->grab($this->comment->html);

        if (!empty($usersId)) {
            $dispatcher->send(
                app(UserRepositoryInterface::class)->findMany($usersId)->exceptUser($this->auth)->exceptUsers($subscribers),
                new UserMentionedNotification($this->comment)
            );
        }

        event(new CommentSaved($this->comment));

        foreach (['name', 'is_blocked', 'is_active', 'photo'] as $key) {
            $this->comment->{$key} = $user->{$key};
        }

        // pass html version of comment to  twig
        $this->comment->text = $this->comment->html;

        // we need to pass is_writeable variable to let know that we are able to edit/delete this comment
        return view('forum.partials.comment', [
            'is_writeable'  => true,
            // get topic's author id
            'author_id'     => $this->topic->firstPost->user_id,
            'comment'       => $this->comment,
            'forum'         => $this->forum
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit()
    {
        $this->authorize('update', [$this->comment, $this->forum]);

        return view('forum.partials.form', [
            'post'      => ['id' => $this->comment->post_id],
            'comment'   => $this->comment
        ]);
    }

    /**
     * @throws \Exception
     */
    public function delete()
    {
        $this->authorize('delete', [$this->comment, $this->forum]);

        $target = (new Stream_Topic())->map($this->topic);
        $object = (new Stream_Comment())->map($this->post, $this->comment, $this->topic);

        $this->transaction(function () use ($object, $target) {
            $this->comment->delete();

            stream(Stream_Delete::class, $object, $target);
        });

        event(new CommentDeleted($this->comment));
    }
}
