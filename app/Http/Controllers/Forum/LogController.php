<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Post;
use Coyote\Services\UrlBuilder;

class LogController extends BaseController
{
    /**
     * @param Post $post
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function log(Post $post)
    {
        $topic = $post->topic;
        $forum = $topic->forum;

        $this->authorize('update', $forum);

        $this->breadcrumb($forum);
        $this->breadcrumb->push([
            $topic->title => UrlBuilder::topic($topic),
            'Historia postu' => route('forum.post.log', [$post->id])
        ]);

        $logs = $this->post->history($post->id);

        $raw = $logs->pluck('text')->toJson();

        /** @var \Coyote\Services\Parser\Factories\AbstractFactory $parser */
        $parser = app('parser.post');
        $parser->cache->setEnable(false);

        foreach ($logs as &$log) {
            $log->text = $parser->parse($log->text);
        }

        return $this->view('forum.log')->with(compact('logs', 'post', 'forum', 'topic', 'raw'));
    }
}
