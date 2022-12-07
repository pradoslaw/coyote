<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Post;
use Coyote\Services\Parser\Parsers\HtmlEntities;
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
            'Historia posta' => route('forum.post.log', [$post->id])
        ]);

        $parser = new HtmlEntities();

        $logs = $this->post->history($post->id)->map(function (Post\Log $post) use ($parser) {
            $post->text = $parser->parse($post->text);

            return $post;
        });

        return $this->view('forum.log')->with(compact('logs', 'post', 'forum', 'topic'));
    }
}
