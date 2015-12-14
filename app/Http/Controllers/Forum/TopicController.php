<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;
use Illuminate\Http\Request;
use Coyote\Http\Requests\PostRequest;

class TopicController extends Controller
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

    /**
     * @param \Coyote\Forum $forum
     * @param int $id
     * @param string $slug
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index($forum, $id, $slug, Request $request)
    {
        $topic = $this->topic->findOrFail($id);
        if ($topic->forum_id !== $forum->id) {
            return redirect(route('forum.topic', [$forum->path, $id, $topic->path]));
        }

        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $id, $topic->path]));

        $viewers = app('Session\Viewers')->render($request->getRequestUri());

        return parent::view('forum.topic')->with('viewers', $viewers);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function submit($forum)
    {
        if (auth()->guest() && !$forum->enable_anonymous) {
            abort(403);
        }

        $this->breadcrumb($forum);
        $this->breadcrumb->push('Nowy wątek', route('forum.topic.submit', [$forum->path]));

        return parent::view('forum.submit')->with('forum', $forum);
    }

    /**
     * @param $forum
     * @param PostRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($forum, PostRequest $request)
    {
        $url = \DB::transaction(function () use ($request, $forum) {
            $path = str_slug($request->get('subject'), '_');

            $topic = $this->topic->create($request->all() + ['path' => $path, 'forum_id' => $forum->id]);
            $this->post->create($request->all() + [
                'user_id'   => auth()->id(),
                'topic_id'  => $topic->id,
                'forum_id'  => $forum->id,
                'ip'        => request()->ip(),
                'browser'   => request()->browser(),
                'host'      => request()->server('SERVER_NAME')
            ]);

            return route('forum.topic', [$forum->path, $topic->id, $path]);
        });

        return redirect()->to($url);
    }
}
