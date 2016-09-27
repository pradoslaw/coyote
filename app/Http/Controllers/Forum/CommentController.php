<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Services\Alert\Container;
use Coyote\Http\Controllers\Controller;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Objects\Comment as Stream_Comment;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
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

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        // set variables from middleware
        foreach ($request->attributes->keys() as $key) {
            $this->$key = $request->attributes->get($key);
        }
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

        $target = (new Stream_Topic())->map($this->topic);

        if ($id === null) {
            $user = auth()->user();
            $data = $request->only(['text']) + ['user_id' => $user->id, 'post_id' => $request->input('post_id')];

            $activity = Stream_Create::class;
        } else {
            $this->authorize('update', [$this->comment, $this->forum]);

            $user = app(UserRepositoryInterface::class)->find(
                $this->comment->user_id,
                ['id', 'name', 'is_blocked', 'is_active', 'photo']
            );
            $data = $request->only(['text']);

            $activity = Stream_Update::class;
        }

        $this->comment->fill($data);

        $this->transaction(function () use ($id, $activity, $target) {
            $this->comment->save();

            // we need to parse text first (and store it in cache)
            /** @var \Coyote\Services\Parser\Parsers\ParserInterface $parser */
            $parser = app('parser.comment')->setUserId($this->userId);
            $this->comment->text = $parser->parse($this->comment->text);

            // it is IMPORTANT to parse text first, and then put information to activity stream.
            // so that we will save plan text (without markdown)
            $object = (new Stream_Comment())->map($this->post, $this->comment, $this->forum, $this->topic);
            stream($activity, $object, $target);

            if (!$id) {
                $alert = new Container();
                $notification = [
                    'sender_id'   => $this->userId,
                    'sender_name' => auth()->user()->name,
                    'subject'     => excerpt($this->topic->subject),
                    'excerpt'     => excerpt($this->comment->text),
                    'url'         => $object->url
                ];

                $subscribersId = $this->forum->onlyUsersWithAccess(
                    $this->post->subscribers()->lists('user_id')->toArray()
                );

                if ($subscribersId) {
                    $alert->attach(
                        // $subscribersId can be int or array. we need to cast to array type
                        app('alert.post.subscriber')->with($notification)->setUsersId($subscribersId)
                    );
                }

                // get id of users that were mentioned in the text
                $subscribersId = $this->forum->onlyUsersWithAccess((new LoginHelper())->grab($this->comment->text));

                if ($subscribersId) {
                    $alert->attach(
                        app('alert.post.comment.login')->with($notification)->setUsersId($subscribersId)
                    );
                }

                $alert->notify();

                // subscribe post. notify about all future comments to this post
                $this->post->subscribe($this->userId, true);
            }
        });

        foreach (['name', 'is_blocked', 'is_active', 'photo'] as $key) {
            $this->comment->$key = $user->$key;
        }

        // we need to pass is_writeable variable to let know that we are able to edit/delete this comment
        return view('forum.partials.comment', [
            'is_writeable' => true,
            'comment' => $this->comment,
            'forum' => $this->forum
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit()
    {
        $this->authorize('update', [$this->comment, $this->forum]);

        return view('forum.partials.form', [
            'post' => ['id' => $this->comment->post_id],
            'comment' => $this->comment
        ]);
    }

    /**
     * @throws \Exception
     */
    public function delete()
    {
        $this->authorize('delete', [$this->comment, $this->forum]);

        $target = (new Stream_Topic())->map($this->topic);
        $object = (new Stream_Comment())->map($this->post, $this->comment, $this->forum, $this->topic);

        $this->transaction(function () use ($object, $target) {
            $this->comment->delete();

            stream(Stream_Delete::class, $object, $target);
        });
    }
}
