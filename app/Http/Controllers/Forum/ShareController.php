<?php
namespace Coyote\Http\Controllers\Forum;

use Coyote\Post;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\RedirectResponse;

class ShareController extends BaseController
{
    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function index($id)
    {
        /** @var Post $post */
        $post = $this->post->withTrashed()->find($id, ['id', 'topic_id', 'forum_id', 'deleted_at']);
        if (!$post || !$post->topic) {
            abort(404);
        }

        /** @var Gate $gate */
        $gate = app(Gate::class);
        if ($post->deleted_at !== null && $gate->denies('delete', $post->forum)) {
            abort(404);
        }

        $route = route('forum.topic', [$post->forum->slug, $post->topic->id, $post->topic->slug]);
        return redirect($route . '?p=' . $id . '#id' . $id);
    }
}
