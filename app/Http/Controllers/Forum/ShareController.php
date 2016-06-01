<?php

namespace Coyote\Http\Controllers\Forum;

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
        /** @var \Coyote\Post $post */
        $post = $this->post->withTrashed()->find($id, ['id', 'topic_id', 'forum_id', 'deleted_at']);
        if (!$post) {
            abort(404);
        }

        if ($post->deleted_at !== null && $this->getGateFactory()->denies('delete', $post->forum)) {
            abort(404);
        }

        $route = route('forum.topic', [$post->forum->slug, $post->topic->id, $post->topic->slug]);
        return redirect($route . '?p=' . $id . '#id' . $id);
    }
}
