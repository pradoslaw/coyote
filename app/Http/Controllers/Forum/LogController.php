<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Post;
use Coyote\Services\UrlBuilder;
use Illuminate\View\View;

class LogController extends BaseController
{
    public function log(Post $post): View
    {
        $this->authorize('update', $post->topic->forum);
        $this->breadcrumb($post->topic->forum);
        $this->breadcrumb->push([
            $post->topic->title => UrlBuilder::topic($post->topic),
            'Historia posta'    => route('forum.post.log', [$post->id]),
        ]);
        $logs = $this->post->history($post->id)->map(function (Post\Log $log): Post\Log {
            $log->text = \htmlEntities($log->text);
            return $log;
        });
        return $this->view('forum.log')->with([
            'logs'  => $logs,
            'post'  => $post,
            'forum' => $post->topic->forum,
            'topic' => $post->topic,
        ]);
    }
}
