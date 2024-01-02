<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Post;
use Coyote\Services\UrlBuilder;
use Illuminate\Database\Eloquent;
use Illuminate\View\View;

class LogController extends BaseController
{
    public function log(int $postId): View
    {
        $post = $this->postById($postId);
        if ($post === null) {
            abort(404);
        }
        $topic = $post->topic;
        $forum = $topic->forum;
        $this->authorize('update', $forum);
        $this->breadcrumb($forum);
        $this->breadcrumb->pushMany([
            $topic->title    => UrlBuilder::topic($topic),
            'Historia posta' => route('forum.post.log', [$post->id]),
        ]);
        return $this->view('forum.log')->with([
            'logs'      => $this->postLogs($post->id),
            'post'      => $post,
            'forum'     => $forum,
            'topic'     => $topic,
            'topicLink' => route('forum.topic', [$forum->slug, $topic->id, $topic->slug]) . '?p=' . $post->id . '#id' . $post->id,
        ]);
    }

    private function postById(int $id): ?Post
    {
        $post = Post::withTrashed()->find($id);
        if ($post === null) {
            return null;
        }
        if ($post->deleted_at === null) {
            return $post;
        }
        if ($this->getGateFactory()->allows('delete', $post->topic->forum)) {
            return $post;
        }
        return null;
    }

    public function postLogs(int $postId): Eloquent\Collection
    {
        return Post\Log::query()
            ->select(['post_log.id', 'post_log.*'])
            ->where('post_id', $postId)
            ->join('posts', 'posts.id', '=', 'post_id')
            ->orderBy('post_log.id', 'DESC')
            ->with('user')
            ->get();
    }
}
