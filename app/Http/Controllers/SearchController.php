<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\Api;
use Coyote\Services\Elasticsearch\Builders\MixedBuilder;
use Coyote\Services\Elasticsearch\MultiResultSet;
use Coyote\Services\Elasticsearch\Search;
use Coyote\Services\Elasticsearch\Strategies\CommonStrategy;
use Coyote\Services\Elasticsearch\Strategies\TopicStrategy;
use Coyote\Topic;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Lavary\Menu\Builder;
use Lavary\Menu\Item;
use Lavary\Menu\Menu;

class SearchController extends Controller
{
    private $api;

    public function __construct(Api $api)
    {
        parent::__construct();

        $this->api = $api;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index(Request $request, Search $search)
    {
        $this->breadcrumb->push('Szukaj', route('search'));

        switch ($request->input('model')) {
            case class_basename(Topic::class):
                $strategy = app(TopicStrategy::class);
                break;

            default:
                $strategy = new CommonStrategy();

        }

        start_measure('elasticsearch');
        $hits = $search->search($strategy);
        stop_measure('elasticsearch');

        if ($request->wantsJson()) {
            return $hits;
        }

        return $this->view('search', ['hits' => $hits, 'model' => $request->input('model')])->with('tabs', $this->tabs());
    }

    /**
     * @return array
     */
    private function search()
    {
        if (!$this->request->filled('q')) {
            return [];
        }

        // search only in allowed forum categories
        $this->forum->pushCriteria(new OnlyThoseWithAccess($this->auth));
        $this->request->attributes->set('forum_id', $this->forum->pluck('id'));

        // build elasticsearch request
        $body = (new MixedBuilder($this->request))->build();

        $params = [
            'index'     => config('elasticsearch.default_index'),
            'type'      => '_doc',
            'body'      => $body
        ];

        debugbar()->debug(json_encode($body));
        debugbar()->startMeasure('elasticsearch');

        // do the search and transform results
        $hits = new MultiResultSet($this->getClient()->search($params));
        debugbar()->stopMeasure('elasticsearch');

        $pagination = new LengthAwarePaginator($hits, $hits->total(), 10, null, ['path' => ' ']);
        $pagination->appends($this->request->except('page'));

        return [
            'hits' => $hits,
            'took' => $hits->took(),
            'total' => $hits->total(),
            'pagination' => $pagination
        ];
    }

    /**
     * @return mixed
     */
    private function tabs()
    {
        return app(Menu::class)->make('tabs', function (Builder $menu) {
            $item = $menu->add('Wszystko', ['class' => 'nav-item', 'url' => route('search', ['q' => $this->request->input('q')])]);
            $item->link->attr(['class' => 'nav-link']);

            foreach (['Forum' => MixedBuilder::TOPIC, 'Praca' => MixedBuilder::JOB, 'Mikroblog' => MixedBuilder::MICROBLOG, 'Kompendium' => MixedBuilder::WIKI] as $label => $type) {
                $item = $menu->add($label, ['class' => 'nav-item', 'url' => $this->route($type)])->data('type', $type);
                $item->link->attr(['class' => 'nav-link']);
            }
        })
        ->filter(function (Item $item) {
            if ($this->request->input('type') === $item->data('type')) {
                $item->link->active();
            }

            return true;
        });
    }

    /**
     * @param string$type
     * @param string $type
     * @return string
     */
    private function route($type)
    {
        return route('search', ['type' => $type, 'q' => $this->request->input('q')]);
    }
}
