<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;

/**
 * Class ShareController
 * @package Coyote\Http\Controllers\Forum
 */
class ShareController extends BaseController
{
    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
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
