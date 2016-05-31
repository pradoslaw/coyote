<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Factories\StreamFactory;
use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as StreamRepository;

class StreamController extends BaseController
{
    use StreamFactory;

    /**
     * @param \Coyote\Topic $topic
     * @param StreamRepository $stream
     * @param PageRepository $page
     * @return mixed
     */
    public function index($topic, StreamRepository $stream, PageRepository $page)
    {
        $this->authorize('update', $topic->forum);

        $activities = $stream->takeForTopic($topic->id);
        $collection = $activities->items();

        // nie wiem czemu przy zastosowaniu pagination() musze tutaj rzutowac te elementy na array
        // natomiast przy get() nie :/ jakis wtf, ale nie mam czasu tego analizowac
        foreach ($collection as &$item) {
            $item['object'] = (array) $item['object'];
            $item['target'] = (array) $item['target'];
            $item['actor'] = (array) $item['actor'];
        }

        $decorate = $this->getStreamFactory()->decorate($collection);

        $visits = $page->visits($topic->page()->getResults()->id);
//dd($visits);
        $this->breadcrumb($topic->forum);
        $this->breadcrumb->push($topic->subject, route('forum.topic', [$topic->forum->slug, $topic->id, $topic->slug]));
        $this->breadcrumb->push('Dziennik zdarzeń', route('forum.stream', [$topic->id]));

        return $this->view('forum.stream')->with(compact('topic', 'forum', 'activities', 'decorate', 'visits'));
    }
}
