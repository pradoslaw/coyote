<?php

namespace Coyote\Http\Controllers;

use Coyote\Services\Elasticsearch\Factories\GeneralFactory;
use Coyote\Services\Elasticsearch\Transformers\GeneralTransformer;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Lavary\Menu\Menu;

class SearchController extends Controller
{
    /**
     * @var Request
     */
    private $request;

    /**
     * SearchController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        $this->request = $request;
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
        if (!$this->request->exists('q')) {
            return [];
        }

        // build elasticsearch request
        $builder = (new GeneralFactory())->build($this->request);
        $body = $builder->build();

        $params = [
            'index'     => config('elasticsearch.default_index'),
            'type'      => 'topics,microblogs,wiki,jobs',
            'body'      => $body
        ];

        debugbar()->debug(json_encode($body));
        // do the search and transform results
        $hits = new GeneralTransformer($this->getClient()->search($params));

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
        return app(Menu::class)->make('tabs', function ($menu) {
            $menu->add('Posty na forum', $this->route('posts'));
            $menu->add('Oferty pracy', $this->route('offers'));
            $menu->add('Mikroblogi', $this->route('microblogs'));
            $menu->add('Artykuły', $this->route('wiki'));
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
