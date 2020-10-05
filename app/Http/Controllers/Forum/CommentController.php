<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Events\CommentDeleted;
use Coyote\Events\CommentSaved;
use Coyote\Http\Resources\PostCommentResource;
use Coyote\Notifications\Post\Comment\UserMentionedNotification;
use Coyote\Notifications\Post\CommentedNotification;
use Coyote\Post;
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
     * @param null $id
     * @return PostCommentResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
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

        $this->comment->setRelation('forum', $this->forum);

        event(new CommentSaved($this->comment));

        PostCommentResource::withoutWrapping();

        return new PostCommentResource($this->comment);
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

    /**
     * @param Post $post
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Post $post)
    {
        $this->authorize('access', [$post->forum]);

        PostCommentResource::withoutWrapping();

        $post->load('comments.user');

        $post->comments->each(function (Post\Comment $comment) use ($post) {
            $comment->setRelation('forum', $post->forum);
        });

        return PostCommentResource::collection($post->comments)->keyBy('id');
    }
}
