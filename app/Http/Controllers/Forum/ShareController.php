<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as Topic;

/**
 * Class ShareController
 * @package Coyote\Http\Controllers\Forum
 */
class ShareController extends Controller
{
    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var Post
     */
    private $post;

    /**
     * @var Topic
     */
    private $topic;

    /**
     * @param Forum $forum
     * @param Post $post
     * @param Topic $topic
     */
    public function __construct(Forum $forum, Post $post, Topic $topic)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->post = $post;
        $this->topic = $topic;
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index($id)
    {
        $post = $this->post->withTrashed()->find($id, ['id', 'topic_id', 'forum_id', 'deleted_at']);
        if (!$post) {
            abort(404);
        }

        $forum = $this->forum->find($post->forum_id, ['id', 'path']);

        if ($post->deleted_at !== null && Gate::denies('delete', $forum)) {
            abort(404);
        }

        $topic = $this->topic->find($post->topic_id, ['id', 'path']);
        $url = route('forum.topic', [$forum->path, $topic->id, $topic->path]) . '?p=' . $id . '#id' . $id;

        return redirect($url);
    }
}
