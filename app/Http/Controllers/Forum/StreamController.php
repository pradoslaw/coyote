<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as StreamRepository;
use Coyote\Services\Stream\Renderer;
use Coyote\Services\UrlBuilder\UrlBuilder;

class StreamController extends BaseController
{
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

        $decorate = (new Renderer($collection))->render();

        $visits = $page->visits($topic->page()->getResults()->id);

        $this->breadcrumb($topic->forum);
        $this->breadcrumb->push([
            $topic->subject => UrlBuilder::topic($topic),
            'Dziennik zdarzeń' => route('forum.stream', [$topic->id])
        ]);

        return $this->view('forum.stream')->with(compact('topic', 'activities', 'decorate', 'visits'));
    }
}
