<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\Builders\MixedBuilder;
use Coyote\Services\Elasticsearch\MultiResultSet;
use Illuminate\Pagination\LengthAwarePaginator;
use Lavary\Menu\Builder;
use Lavary\Menu\Item;
use Lavary\Menu\Menu;

class SearchController extends Controller
{
    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @param ForumRepository $forum
     */
    public function __construct(ForumRepository $forum)
    {
        parent::__construct();

        $this->forum = $forum;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Szukaj', route('search'));

        return $this->view('search', $this->search())->with('tabs', $this->tabs());
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
     * Get client instance
     *
     * @return mixed
     */
    protected function getClient()
    {
        return app('elasticsearch');
    }

    /**
     * @return mixed
     */
    private function tabs()
    {
        return app(Menu::class)->make('tabs', function (Builder $menu) {
            $menu->add('Wszystko', route('search', ['q' => $this->request->input('q')]));

            foreach (['Forum' => MixedBuilder::TOPIC, 'Praca' => MixedBuilder::JOB, 'Mikroblog' => MixedBuilder::MICROBLOG, 'Kompendium' => MixedBuilder::WIKI] as $label => $type) {
                $menu->add($label, $this->route($type))->data('type', $type);
            }
        })
        ->filter(function (Item $item) {
            if ($this->request->input('type') === $item->data('type')) {
                $item->active();
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
