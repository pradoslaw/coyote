<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\StreamRepositoryInterface as Stream;

class StreamController extends BaseController
{
    /**
     * @param $topic
     * @param Stream $stream
     * @return mixed
     */
    public function index($topic, Stream $stream)
    {
        $forum = $this->forum->find($topic->forum_id, ['path', 'name']);
        $this->authorize('update', $forum);

        $activities = $stream->whereNested(function ($query) use ($topic) {
            $query->where('target.objectType', 'topic')
                  ->where('target.id', $topic->id);
        })
        ->whereNested(function ($query) use ($topic) {
            $query->where('object.objectType', 'topic')
                  ->where('object.id', $topic->id);
        }, 'or')
        ->orderBy('_id', 'DESC')
        ->paginate();

        $decorate = app('Stream')->decorate($activities);

        $this->breadcrumb($forum);
        $this->breadcrumb->push('Dziennik zdarzeń', route('forum.stream', [$topic->id]));

        return $this->view('forum.stream')->with(compact('topic', 'forum', 'activities', 'decorate'));
    }
}
