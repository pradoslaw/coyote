<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as StreamRepository;
use Coyote\Services\Elasticsearch\Builders\Stream\TopicBuilder;
use Coyote\Services\Stream\Renderer;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Illuminate\Pagination\Paginator;

class StreamController extends BaseController
{
    /**
     * @param Topic $topic
     * @param StreamRepository $stream
     * @param PageRepository $page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Topic $topic, StreamRepository $stream, PageRepository $page)
    {
        $this->authorize('update', $topic->forum);

        $builder = (new TopicBuilder($this->request))->setTopicId($topic->id);
        $result = $stream->search($builder);

        $paginator = new Paginator(
            $result->getSource(),
            TopicBuilder::PER_PAGE,
            $this->request->get('page'),
            ['path' => Paginator::resolveCurrentPath()],
        );

        (new Renderer($paginator->items()))->render();

        $this->breadcrumb($topic->forum);
        $this->breadcrumb->pushMany([
            $topic->title      => UrlBuilder::topic($topic),
            'Dziennik zdarzeń' => route('forum.stream', [$topic->id]),
        ]);

        return $this->view('forum.stream')->with([
            'topic'     => $topic,
            'paginator' => $paginator,
        ]);
    }
}
