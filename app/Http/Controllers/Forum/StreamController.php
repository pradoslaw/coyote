<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Factories\StreamFactory;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as Stream;

class StreamController extends BaseController
{
    use StreamFactory;

    /**
     * @param $topic
     * @param Stream $stream
     * @return mixed
     */
    public function index($topic, Stream $stream)
    {
        $forum = $this->forum->find($topic->forum_id, ['id', 'path', 'name', 'parent_id']);
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

        $collection = $activities->items();

        // nie wiem czemu przy zastosowaniu pagination() musze tutaj rzutowac te elementy na array
        // natomiast przy get() nie :/ jakis wtf, ale nie mam czasu tego analizowac
        foreach ($collection as &$item) {
            $item['object'] = (array) $item['object'];
            $item['target'] = (array) $item['target'];
            $item['actor'] = (array) $item['actor'];
        }

        $decorate = $this->getStreamFactory()->decorate($collection);

        $this->breadcrumb($forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->path, $topic->id, $topic->path]));
        $this->breadcrumb->push('Dziennik zdarzeń', route('forum.stream', [$topic->id]));

        return $this->view('forum.stream')->with(compact('topic', 'forum', 'activities', 'decorate'));
    }
}
