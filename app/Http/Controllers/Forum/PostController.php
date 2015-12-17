<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\PostRequest;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Coyote\Parser\Reference\Login as Ref_Login;

class PostController extends Controller
{
    use Base;

    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var Topic
     */
    private $topic;

    /**
     * @var Post
     */
    private $post;

    /**
     * @param Forum $forum
     * @param Topic $topic
     * @param Post $post
     */
    public function __construct(Forum $forum, Topic $topic, Post $post)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->topic = $topic;
        $this->post = $post;
    }

    public function submit($forum, $topic, $post = null)
    {
        // make sure that user can write in this category
        $this->authorizeForum($forum);

        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $topic->id, $topic->path]));

        if ($post === null) {
            $this->breadcrumb->push('Odpowiedz', request()->path());
            $title = 'Napisz odpowiedź w wątku ' . $topic->subject;
        } else {
            // make sure user can edit this post
            $this->authorize('update', [$post, $forum]);

            $this->breadcrumb->push('Edycja', request()->path());
            $title = 'Edycja posta';
        }

        return parent::view('forum.submit')->with(compact('forum', 'topic', 'post', 'title'));
    }

    public function save(PostRequest $request, $forum, $topic, $post = null)
    {
        $this->authorizeForum($forum);

        // parsing text and store it in cache
        $text = app()->make('Parser\Post')->parse($request->text);

        if ($post !== null) {
            $this->authorize('update', [$post, $forum]);
            $data = $request->only(['text', 'user_name']) + [
                    'edit_count' => $post->edit_count + 1, 'editor_id' => auth()->id()
                ];

            $post->fill($data)->save();

            if ($post->id === $topic->first_post_id) {
                $path = str_slug($request->get('subject'), '_');

                $topic->fill($request->all() + ['path' => $path])->save();
            }
        } else {
            // create new post and assign it to topic. don't worry about the rest: trigger will do the work
            $post = $this->post->create($request->all() + [
                'user_id'   => auth()->id(),
                'topic_id'  => $topic->id,
                'forum_id'  => $forum->id,
                'ip'        => request()->ip(),
                'browser'   => request()->browser(),
                'host'      => request()->server('SERVER_NAME')
            ]);

            // get id of users that were mentioned in the text
            $usersId = (new Ref_Login())->grab($text);

            if ($usersId) {
                app()->make('Alert\Post\Login')->with([
                    'users_id'    => $usersId,
                    'sender_id'   => auth()->id(),
                    'sender_name' => $request->get('user_name', auth()->user()->name),
                    'subject'     => excerpt($topic->subject, 48),
                    'excerpt'     => excerpt($text),
                    'url'         => route('forum.topic', [$forum->path, $topic->id, $topic->path], false)
                ])->notify();
            }
        }

        return redirect(route('forum.topic', [$forum->path, $topic->id, $topic->path]) . '#id' . $post->id);
    }
}
